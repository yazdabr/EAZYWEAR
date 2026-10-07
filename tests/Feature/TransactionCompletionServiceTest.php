<?php

namespace Tests\Feature;

use App\Mail\OrderCompletedMail;
use App\Models\Transaction;
use App\Services\TransactionCompletionService;
use App\Models\TransactionNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TransactionCompletionServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_service_can_complete_pickup_order(): void
    {
        Mail::fake();

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

        Mail::assertSent(OrderCompletedMail::class, 1);

        $this->assertDatabaseHas('transaction_notifications', [
            'transaction_id' => $transaction->id,
            'type' => 'ORDER_COMPLETED_EMAIL',
        ]);
    }

    public function test_pickup_completion_email_contains_pickup_schedule(): void
    {
        Mail::fake();

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_PROCESSING,
            'shipping_method' => 'Ambil di Tempat',
            'shipping_email' => 'customer@example.com',
            'pickup_date' => '2026-10-30',
            'pickup_time_start' => '09:00:00',
            'pickup_time_end' => '11:00:00',
        ]);

        app(TransactionCompletionService::class)
            ->completeManually($transaction);

        Mail::assertSent(
            OrderCompletedMail::class,
            function (OrderCompletedMail $mail) {
                $html = $mail->render();

                return str_contains($html, 'Pesanan Siap Diambil')
                    && str_contains($html, 'Ambil di Tempat')
                    && str_contains($html, '30 Oktober 2026')
                    && str_contains($html, '09:00')
                    && str_contains($html, '11:00')
                    && str_contains($html, 'WITA')
                    && str_contains(
                        $html,
                        'Silakan ambil pesanan Anda sesuai dengan jadwal pengambilan'
                    );
            }
        );
    }

    public function test_service_can_complete_courier_order(): void
    {
        Mail::fake();

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

        Mail::assertSent(OrderCompletedMail::class, 1);
    }

    public function test_manual_completion_rejects_invalid_status(): void
    {
        Mail::fake();

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_PROCESSING,
            'shipping_method' => 'Kurir',
            'shipping_email' => 'customer@example.com',
        ]);

        $this->expectException(ValidationException::class);

        app(TransactionCompletionService::class)
            ->completeManually($transaction);

        Mail::assertNothingSent();
    }

    public function test_biteship_completion_is_idempotent(): void
    {
        Mail::fake();

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

        Mail::assertSent(OrderCompletedMail::class, 1);

        $service->completeFromBiteship($transaction->fresh());

        Mail::assertSent(OrderCompletedMail::class, 1);

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

    public function test_biteship_completion_retries_failed_completion_email(): void
    {
        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'shipping_email' => 'customer@example.com',
            'biteship_order_id' => 'BITESHIP-TEST-RETRY',
            'biteship_status' => 'delivered',
        ]);

        $service = app(TransactionCompletionService::class);

        /*
        * Percobaan pertama: email gagal.
        */
        Mail::shouldReceive('to')
            ->once()
            ->with('customer@example.com')
            ->andThrow(new \RuntimeException('SMTP test failure'));

        $completed = $service->completeFromBiteship($transaction);

        $this->assertSame(
            Transaction::ORDER_COMPLETED,
            $completed->status
        );

        $notification = \App\Models\TransactionNotification::query()
            ->where('transaction_id', $transaction->id)
            ->where('type', 'ORDER_COMPLETED_EMAIL')
            ->first();

        $this->assertNotNull($notification);
        $this->assertNull($notification->sent_at);

        /*
        * Percobaan kedua: email berhasil.
        *
        * Karena Mail sudah menjadi Mockery mock, kita tidak
        * memanggil Mail::fake() lagi.
        */
        $mailer = \Mockery::mock();

        $mailer
            ->shouldReceive('send')
            ->once()
            ->with(\Mockery::type(OrderCompletedMail::class));

        Mail::shouldReceive('to')
            ->once()
            ->with('customer@example.com')
            ->andReturn($mailer);

        $service->completeFromBiteship($transaction->fresh());

        $notification->refresh();

        $this->assertNotNull($notification->sent_at);

        $this->assertSame(
            1,
            \Illuminate\Support\Facades\DB::table('order_status_histories')
                ->where('transaction_id', $transaction->id)
                ->where('status', Transaction::ORDER_COMPLETED)
                ->count()
        );
    }

    public function test_biteship_completion_does_not_complete_pickup_order(): void
    {
        Mail::fake();

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

        Mail::assertNothingSent();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => Transaction::ORDER_PROCESSING,
        ]);
    }

    public function test_completion_email_failure_does_not_rollback_completed_status(): void
    {
        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new \RuntimeException('SMTP test failure'));

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
        $this->assertNull($notification->sent_at);
    }
}
