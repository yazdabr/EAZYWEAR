<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Services\TransactionExpiryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Mail\PaymentExpiredMail;
use Illuminate\Support\Facades\Mail;
use App\Models\FulfillmentHold;
use App\Models\FulfillmentSlot;

class TransactionExpiryServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        FulfillmentHold::query()->delete();

        FulfillmentSlot::query()
            ->whereBetween('date', [
                '2026-10-30',
                '2026-11-03',
            ])
            ->update([
                'used_count' => 0,
                'capacity' => 100,
            ]);
    }

    public function test_pending_transaction_expires_after_va_deadline(): void
    {
        $transaction = Transaction::factory()->create([
            'status' => 'PENDING',
            'va_expired_at' => now('UTC')->subMinute(),
        ]);

        $result = app(TransactionExpiryService::class)->expire($transaction);

        $this->assertTrue($result);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'EXPIRED',
        ]);
    }

    public function test_pending_transaction_does_not_expire_before_va_deadline(): void
    {
        $transaction = Transaction::factory()->create([
            'status' => 'PENDING',
            'va_expired_at' => now('UTC')->addMinute(),
        ]);

        $result = app(TransactionExpiryService::class)->expire($transaction);

        $this->assertFalse($result);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'PENDING',
        ]);
    }

    public function test_pending_qris_transaction_expires_after_qris_deadline(): void
    {
        $transaction = Transaction::factory()->create([
            'status' => 'PENDING',
            'payment_method' => 'QRIS',
            'va_expired_at' => null,
            'qris_expired_at' => now('UTC')->subMinute(),
        ]);

        $result = app(TransactionExpiryService::class)->expire($transaction);

        $this->assertTrue($result);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'EXPIRED',
        ]);
    }

    public function test_pending_qris_transaction_does_not_expire_before_qris_deadline(): void
    {
        $transaction = Transaction::factory()->create([
            'status' => 'PENDING',
            'payment_method' => 'QRIS',
            'va_expired_at' => null,
            'qris_expired_at' => now('UTC')->addMinute(),
        ]);

        $result = app(TransactionExpiryService::class)->expire($transaction);

        $this->assertFalse($result);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'PENDING',
        ]);
    }

    public function test_paid_transaction_does_not_expire(): void
    {
        $transaction = Transaction::factory()->create([
            'status' => 'PAID',
            'va_expired_at' => now('UTC')->subMinute(),
        ]);

        $result = app(TransactionExpiryService::class)->expire($transaction);

        $this->assertFalse($result);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'PAID',
        ]);
    }

    public function test_cancelled_transaction_does_not_expire(): void
    {
        $transaction = Transaction::factory()->create([
            'status' => 'CANCELLED',
            'va_expired_at' => now('UTC')->subMinute(),
        ]);

        $result = app(TransactionExpiryService::class)->expire($transaction);

        $this->assertFalse($result);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'CANCELLED',
        ]);
    }

    public function test_expired_transaction_remains_expired(): void
    {
        $transaction = Transaction::factory()->create([
            'status' => 'EXPIRED',
            'va_expired_at' => now('UTC')->subMinute(),
        ]);

        $result = app(TransactionExpiryService::class)->expire($transaction);

        $this->assertFalse($result);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'EXPIRED',
        ]);
    }

    public function test_expiring_transaction_does_not_change_stock_or_create_stock_movement(): void
    {
        $inventory = Inventory::query()->firstOrFail();

        $initialStock = (int) $inventory->stock;
        $initialMovementCount = StockMovement::query()->count();

        $transaction = Transaction::factory()->create([
            'status' => 'PENDING',
            'va_expired_at' => now('UTC')->subMinute(),
        ]);

        $result = app(TransactionExpiryService::class)->expire($transaction);

        $this->assertTrue($result);

        $this->assertSame(
            $initialStock,
            (int) $inventory->fresh()->stock
        );

        $this->assertSame(
            $initialMovementCount,
            StockMovement::query()->count()
        );
    }

    public function test_expiring_qris_transaction_sends_payment_expired_email(): void
    {
        Mail::fake();

        $transaction = Transaction::factory()->create([
            'status' => 'PENDING',
            'payment_method' => 'QRIS',
            'va_expired_at' => null,
            'qris_expired_at' => now('UTC')->subMinute(),
        ]);

        $result = app(TransactionExpiryService::class)->expire($transaction);

        $this->assertTrue($result);

        Mail::assertSent(PaymentExpiredMail::class, function ($mail) use ($transaction) {
            return $mail->transaction->id === $transaction->id;
        });
    }
    public function test_expiring_special_batch_transaction_releases_fulfillment_hold(): void
    {
        $transaction = Transaction::factory()->create([
            'status' => 'PENDING',
            'va_expired_at' => now('UTC')->subMinute(),
            'fulfillment_date' => '2026-10-30',
        ]);

        $slot = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->firstOrFail();

        $hold = FulfillmentHold::query()->create([
            'transaction_id' => $transaction->id,
            'fulfillment_slot_id' => $slot->id,
            'status' => FulfillmentHold::HELD,
        ]);

        $result = app(TransactionExpiryService::class)->expire($transaction);

        $this->assertTrue($result);

        $transaction->refresh();
        $hold->refresh();
        $slot->refresh();

        $this->assertSame('EXPIRED', $transaction->status);
        $this->assertSame(
            FulfillmentHold::RELEASED,
            $hold->status
        );
        $this->assertNotNull($hold->released_at);
        $this->assertSame(0, (int) $slot->used_count);
    }
}
