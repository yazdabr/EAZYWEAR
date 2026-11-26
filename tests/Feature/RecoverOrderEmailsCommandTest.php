<?php

namespace Tests\Feature;

use App\Jobs\SendOrderCreatedEmail;
use App\Models\OrderEmailOutbox;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RecoverOrderEmailsCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_command_does_not_recover_alfamart_email_without_payment_code(): void
    {
        Queue::fake();

        $transaction = Transaction::factory()->create([
            'payment_method' => 'ALFAMART',
            'minimarket_payment_code' => null,
        ]);

        $outbox = OrderEmailOutbox::create([
            'transaction_id' => $transaction->id,
            'status' => OrderEmailOutbox::STATUS_PENDING,
            'recovery_attempts' => 0,
        ]);

        $exitCode = Artisan::call('emails:recover-orders');

        $this->assertSame(0, $exitCode);

        $this->assertDatabaseHas('order_email_outboxes', [
            'id' => $outbox->id,
            'status' => OrderEmailOutbox::STATUS_PENDING,
            'recovery_attempts' => 0,
        ]);

        Queue::assertNotPushed(SendOrderCreatedEmail::class);
    }

    public function test_command_recovers_alfamart_email_when_payment_code_exists(): void
    {
        Queue::fake();

        $transaction = Transaction::factory()->create([
            'payment_method' => 'ALFAMART',
            'minimarket_payment_code' => '1234567890123456',
        ]);

        $outbox = OrderEmailOutbox::create([
            'transaction_id' => $transaction->id,
            'status' => OrderEmailOutbox::STATUS_PENDING,
            'recovery_attempts' => 0,
        ]);

        $exitCode = Artisan::call('emails:recover-orders');

        $this->assertSame(0, $exitCode);

        $this->assertDatabaseHas('order_email_outboxes', [
            'id' => $outbox->id,
            'status' => OrderEmailOutbox::STATUS_QUEUED,
            'recovery_attempts' => 1,
        ]);

        Queue::assertPushed(SendOrderCreatedEmail::class, 1);
    }
}
