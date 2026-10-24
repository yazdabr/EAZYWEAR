<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\TransactionNotification;
use App\Models\User;
use App\Mail\OrderProcessingMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminProcessAllPaidTransactionsTest extends TestCase
{
    use DatabaseTransactions;

    private function superAdmin(): User
    {
        return User::factory()->create([
            'role' => 'super_admin',
        ]);
    }

    public function test_guest_cannot_process_all_paid_orders(): void
    {
        $response = $this->post(route('admin.transactions.process-all'));

        $response->assertRedirect(route('login'));
    }

    public function test_process_all_only_changes_paid_web_orders(): void
    {
        $admin = $this->superAdmin();

        $paidWebOrder = Transaction::factory()->create([
            'invoice_number' => 'TEST-BULK-PAID-WEB',
            'status' => 'PAID',
            'source' => 'Website',
            'shipping_email' => null,
        ]);

        $pendingWebOrder = Transaction::factory()->create([
            'invoice_number' => 'TEST-BULK-PENDING-WEB',
            'status' => 'PENDING',
            'source' => 'Website',
            'shipping_email' => null,
        ]);

        $paidBoothOrder = Transaction::factory()->create([
            'invoice_number' => 'TEST-BULK-PAID-BOOTH',
            'status' => 'PAID',
            'source' => 'Booth',
            'shipping_email' => null,
        ]);

        $alreadyProcessingOrder = Transaction::factory()->create([
            'invoice_number' => 'TEST-BULK-ALREADY-PROCESSING',
            'status' => 'ORDER_PROCESSING',
            'source' => 'Website',
            'shipping_email' => null,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.transactions.process-all'));

        $response->assertRedirect(route('admin.transactions'))
            ->assertSessionHas('success');

        $this->assertSame(
            'ORDER_PROCESSING',
            $paidWebOrder->fresh()->status
        );

        $this->assertSame(
            'PENDING',
            $pendingWebOrder->fresh()->status
        );

        $this->assertSame(
            'PAID',
            $paidBoothOrder->fresh()->status
        );

        $this->assertSame(
            'ORDER_PROCESSING',
            $alreadyProcessingOrder->fresh()->status
        );

        $this->assertDatabaseHas('order_status_histories', [
            'transaction_id' => $paidWebOrder->id,
            'status' => 'ORDER_PROCESSING',
        ]);
    }

    public function test_processing_paid_order_sends_processing_email_and_records_notification(): void
    {
        Mail::fake();

        $admin = $this->superAdmin();

        $order = Transaction::factory()->create([
            'invoice_number' => 'TEST-BULK-EMAIL',
            'status' => 'PAID',
            'source' => 'Website',
            'shipping_email' => 'bulk-email-test@example.com',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.transactions.process-all'))
            ->assertRedirect(route('admin.transactions'))
            ->assertSessionHas('success');

        $this->assertSame('ORDER_PROCESSING', $order->fresh()->status);

        Mail::assertSent(OrderProcessingMail::class, function ($mail) {
            return $mail->hasTo('bulk-email-test@example.com');
        });

        $this->assertDatabaseHas('transaction_notifications', [
            'transaction_id' => $order->id,
            'type' => 'ORDER_PROCESSING_EMAIL',
        ]);

        $notification = TransactionNotification::query()
            ->where('transaction_id', $order->id)
            ->where('type', 'ORDER_PROCESSING_EMAIL')
            ->firstOrFail();

        $this->assertNotNull($notification->sent_at);
    }
}