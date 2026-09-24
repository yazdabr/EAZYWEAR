<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Services\TransactionExpiryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Inventory;
use App\Models\StockMovement;

class TransactionExpiryServiceTest extends TestCase
{
    use DatabaseTransactions;

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
}