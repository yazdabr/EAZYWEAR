<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\TransactionNotification;
use App\Models\TransactionFulfillment;
use App\Services\TransactionCompletionService;
use App\Jobs\SendOrderCompletedEmail;
use App\Mail\OrderCompletedMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendPickupCompletedEmail;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TransactionCompletionServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_service_can_complete_pickup_order(): void
    {
        Queue::fake();

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_PROCESSING,
            'shipping_method' => 'Ambil di Tempat',
            'shipping_email' => 'customer@example.com',
        ]);

        $completed = app(TransactionCompletionService::class)
            ->completeManually($transaction);

        $this->assertSame(
            Transaction::ORDER_COMPLETED,
            $completed->status
        );

        $this->assertDatabaseHas('order_status_histories', [
            'transaction_id' => $transaction->id,
            'status' => Transaction::ORDER_COMPLETED,
        ]);

        Queue::assertPushedOn(
            'emails',
            SendOrderCompletedEmail::class
        );

        $this->assertDatabaseHas('transaction_notifications', [
            'transaction_id' => $transaction->id,
            'type' => 'ORDER_COMPLETED_EMAIL',
        ]);
    }

    public function test_pickup_completion_email_contains_pickup_schedule(): void
    {
        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_PROCESSING,
            'shipping_method' => 'Ambil di Tempat',
            'shipping_email' => 'customer@example.com',
            'pickup_date' => '2026-10-30',
            'pickup_time_start' => '09:00:00',
            'pickup_time_end' => '11:00:00',
        ]);

        $mail = new OrderCompletedMail($transaction);

        $html = $mail->render();

        $this->assertStringContainsString('Pesanan Siap Diambil', $html);
        $this->assertStringContainsString('Ambil di Tempat', $html);
        $this->assertStringContainsString('30 Oktober 2026', $html);
        $this->assertStringContainsString('09:00', $html);
        $this->assertStringContainsString('11:00', $html);
        $this->assertStringContainsString('WITA', $html);
        $this->assertStringContainsString(
            'Silakan ambil pesanan Anda sesuai dengan jadwal pengambilan',
            $html
        );
    }

    public function test_service_can_complete_courier_order(): void
    {
        Queue::fake();

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'shipping_email' => 'customer@example.com',
        ]);

        $completed = app(TransactionCompletionService::class)
            ->completeManually($transaction);

        $this->assertSame(
            Transaction::ORDER_COMPLETED,
            $completed->status
        );

        $this->assertDatabaseHas('order_status_histories', [
            'transaction_id' => $transaction->id,
            'status' => Transaction::ORDER_COMPLETED,
        ]);

        Queue::assertPushedOn(
            'emails',
            SendOrderCompletedEmail::class
        );
    }

    public function test_manual_completion_rejects_invalid_status(): void
    {
        Queue::fake();

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_PROCESSING,
            'shipping_method' => 'Kurir',
            'shipping_email' => 'customer@example.com',
        ]);

        $this->expectException(ValidationException::class);

        app(TransactionCompletionService::class)
            ->completeManually($transaction);

        Queue::assertNothingPushed();
    }

    public function test_biteship_completion_is_idempotent(): void
    {
        Queue::fake();

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'shipping_email' => 'customer@example.com',
            'biteship_order_id' => 'BITESHIP-TEST-001',
            'biteship_status' => 'dropping_off',
        ]);

        $service = app(TransactionCompletionService::class);

        $service->completeFromBiteship($transaction);

        $transaction->refresh();

        $this->assertSame(
            Transaction::ORDER_COMPLETED,
            $transaction->status
        );

        $service->completeFromBiteship($transaction->fresh());

        $this->assertDatabaseHas('transaction_notifications', [
            'transaction_id' => $transaction->id,
            'type' => 'ORDER_COMPLETED_EMAIL',
            'status' => 'queued',
            'sent_at' => null,
        ]);

        $this->assertSame(
            1,
            TransactionNotification::query()
                ->where('transaction_id', $transaction->id)
                ->where('type', 'ORDER_COMPLETED_EMAIL')
                ->count()
        );

        $this->assertDatabaseHas('order_status_histories', [
            'transaction_id' => $transaction->id,
            'status' => Transaction::ORDER_COMPLETED,
        ]);

        $this->assertSame(
            1,
            DB::table('order_status_histories')
                ->where('transaction_id', $transaction->id)
                ->where('status', Transaction::ORDER_COMPLETED)
                ->count()
        );
    }

    public function test_biteship_completion_creates_only_one_completion_notification(): void
    {
        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'shipping_email' => 'customer@example.com',
            'biteship_order_id' => 'BITESHIP-TEST-RETRY',
            'biteship_status' => 'delivered',
        ]);

        $service = app(TransactionCompletionService::class);

        $service->completeFromBiteship($transaction);
        $service->completeFromBiteship($transaction->fresh());

        $this->assertSame(
            1,
            TransactionNotification::query()
                ->where('transaction_id', $transaction->id)
                ->where('type', 'ORDER_COMPLETED_EMAIL')
                ->count()
        );

        $this->assertDatabaseHas('transaction_notifications', [
            'transaction_id' => $transaction->id,
            'type' => 'ORDER_COMPLETED_EMAIL',
            'status' => 'sent',
        ]);

        $this->assertNotNull(
            TransactionNotification::query()
                ->where('transaction_id', $transaction->id)
                ->where('type', 'ORDER_COMPLETED_EMAIL')
                ->first()
                ->sent_at
        );
    }

    public function test_biteship_completion_does_not_complete_pickup_order(): void
    {
        Queue::fake();

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_PROCESSING,
            'shipping_method' => 'Ambil di Tempat',
            'shipping_email' => 'customer@example.com',
            'biteship_order_id' => 'BITESHIP-TEST-002',
            'biteship_status' => 'delivered',
        ]);

        $this->expectException(ValidationException::class);

        app(TransactionCompletionService::class)
            ->completeFromBiteship($transaction);

        Queue::assertNothingPushed();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => Transaction::ORDER_PROCESSING,
        ]);
    }

    public function test_completion_email_failure_does_not_rollback_completed_status(): void
    {

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'shipping_email' => 'customer@example.com',
            'biteship_order_id' => 'BITESHIP-TEST-003',
            'biteship_status' => 'delivered',
        ]);

        $completed = app(TransactionCompletionService::class)
            ->completeFromBiteship($transaction);

        $this->assertSame(
            Transaction::ORDER_COMPLETED,
            $completed->status
        );

        $notification = \App\Models\TransactionNotification::query()
            ->where('transaction_id', $transaction->id)
            ->where('type', 'ORDER_COMPLETED_EMAIL')
            ->first();

        $this->assertNotNull($notification);
        $this->assertSame('sent', $notification->status);
        $this->assertNotNull($notification->sent_at);
    }

    public function test_manual_completion_of_ready_fulfillment_does_not_complete_transaction_while_pre_order_is_pending(): void
    {
        Queue::fake();

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_PROCESSING,
            'shipping_method' => 'Ambil di Tempat',
            'shipping_email' => 'customer@example.com',
        ]);

        $ready = TransactionFulfillment::create([
            'transaction_id' => $transaction->id,
            'group_type' => TransactionFulfillment::GROUP_READY,
            'shipping_method' => 'Ambil di Tempat',
            'shipping_cost' => 0,
            'status' => TransactionFulfillment::STATUS_PENDING,
        ]);

        TransactionFulfillment::create([
            'transaction_id' => $transaction->id,
            'group_type' => TransactionFulfillment::GROUP_PRE_ORDER,
            'shipping_method' => 'Ambil di Tempat',
            'shipping_cost' => 0,
            'status' => TransactionFulfillment::STATUS_PENDING,
        ]);

        $result = app(TransactionCompletionService::class)
            ->markFulfillmentReadyForPickup($ready);

        $this->assertSame(
            TransactionFulfillment::STATUS_READY_FOR_PICKUP,
            $ready->fresh()->status
        );

        $this->assertSame(
            Transaction::ORDER_PROCESSING,
            $result->status
        );

        Queue::assertNothingPushed();

        $this->assertDatabaseMissing('transaction_notifications', [
            'transaction_id' => $transaction->id,
            'type' => 'ORDER_COMPLETED_EMAIL',
        ]);

        $this->assertDatabaseMissing('transaction_notifications', [
            'transaction_id' => $transaction->id,
            'type' => 'PICKUP_COMPLETED_EMAIL',
        ]);
    }

    public function test_transaction_is_completed_only_after_last_fulfillment_is_completed(): void
    {
        Queue::fake();

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_PROCESSING,
            'shipping_method' => 'Ambil di Tempat',
            'shipping_email' => 'customer@example.com',
        ]);

        $ready = TransactionFulfillment::create([
            'transaction_id' => $transaction->id,
            'group_type' => TransactionFulfillment::GROUP_READY,
            'shipping_method' => 'Ambil di Tempat',
            'shipping_cost' => 0,
            'status' => TransactionFulfillment::STATUS_COMPLETED,
        ]);

        $preOrder = TransactionFulfillment::create([
            'transaction_id' => $transaction->id,
            'group_type' => TransactionFulfillment::GROUP_PRE_ORDER,
            'shipping_method' => 'Ambil di Tempat',
            'shipping_cost' => 0,
            'status' => TransactionFulfillment::STATUS_PENDING,
        ]);

        $service = app(TransactionCompletionService::class);

        // Tahap 1: kelompok terakhir dinyatakan siap diambil.
        $readyTransaction = $service->markFulfillmentReadyForPickup($preOrder);

        $this->assertSame(
            TransactionFulfillment::STATUS_READY_FOR_PICKUP,
            $preOrder->fresh()->status
        );

        $this->assertSame(
            Transaction::ORDER_PROCESSING,
            $readyTransaction->status
        );

        // Email pertama: pemberitahuan pesanan siap diambil.
        $this->assertDatabaseHas('transaction_notifications', [
            'transaction_id' => $transaction->id,
            'type' => 'ORDER_COMPLETED_EMAIL',
            'status' => 'queued',
            'sent_at' => null,
        ]);

        Queue::assertPushedOn('emails', SendOrderCompletedEmail::class);

        // Tahap 2: pelanggan benar-benar mengambil kelompok terakhir.
        $completed = $service->completeFulfillmentManually($preOrder);

        $this->assertSame(
            TransactionFulfillment::STATUS_COMPLETED,
            $ready->fresh()->status
        );

        $this->assertSame(
            TransactionFulfillment::STATUS_COMPLETED,
            $preOrder->fresh()->status
        );

        $this->assertSame(
            Transaction::ORDER_COMPLETED,
            $completed->status
        );

        // Email kedua: ucapan terima kasih setelah pesanan diambil.
        $this->assertDatabaseHas('transaction_notifications', [
            'transaction_id' => $transaction->id,
            'type' => 'PICKUP_COMPLETED_EMAIL',
            'status' => 'queued',
            'sent_at' => null,
        ]);

        Queue::assertPushedOn('emails', SendPickupCompletedEmail::class);

        $this->assertDatabaseHas('order_status_histories', [
            'transaction_id' => $transaction->id,
            'status' => Transaction::ORDER_COMPLETED,
        ]);
    }

    public function test_fulfillment_courier_cannot_be_completed_manually_before_shipped(): void
    {
        Queue::fake();

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_PROCESSING,
            'shipping_method' => 'Kurir',
            'shipping_email' => 'customer@example.com',
        ]);

        $fulfillment = TransactionFulfillment::create([
            'transaction_id' => $transaction->id,
            'group_type' => TransactionFulfillment::GROUP_READY,
            'shipping_method' => 'Kurir',
            'shipping_cost' => 8000,
            'status' => TransactionFulfillment::STATUS_PROCESSING,
        ]);

        try {
            app(TransactionCompletionService::class)
                ->completeFulfillmentManually($fulfillment);

            $this->fail('Fulfillment kurir yang belum dikirim seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'fulfillment',
                $exception->errors()
            );
        }

        $this->assertSame(
            TransactionFulfillment::STATUS_PROCESSING,
            $fulfillment->fresh()->status
        );

        $this->assertSame(
            Transaction::ORDER_PROCESSING,
            $transaction->fresh()->status
        );

        Queue::assertNothingPushed();
    }
}
