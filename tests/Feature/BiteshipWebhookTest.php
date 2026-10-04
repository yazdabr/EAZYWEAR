<?php

namespace Tests\Feature;

use App\Mail\OrderCompletedMail;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class BiteshipWebhookTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'biteship.webhook.signature_key' => 'X-Eazywear-Biteship-Signature',
            'biteship.webhook.signature_secret' => 'test-secret-value',
        ]);
    }

    private function webhookHeaders(): array
    {
        return [
            'X-Eazywear-Biteship-Signature' => 'test-secret-value',
        ];
    }

    private function biteshipOrderId(string $suffix): string
    {
        return 'BITESHIP-' . $suffix . '-' . Str::uuid();
    }

    public function test_on_hold_can_be_entered_from_in_transit(): void
    {
        $orderId = $this->biteshipOrderId('EDGE-ON-HOLD-001');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'in_transit',
        ]);

        $response = $this->withHeaders($this->webhookHeaders())
            ->postJson('/webhooks/biteship', [
                'event' => 'order.status',
                'order_id' => $orderId,
                'status' => 'on_hold',
            ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame('on_hold', $transaction->biteship_status);
        $this->assertSame(Transaction::ORDER_SHIPPED, $transaction->status);
    }

    public function test_on_hold_can_resume_to_in_transit(): void
    {
        $orderId = $this->biteshipOrderId('EDGE-ON-HOLD-002');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'on_hold',
        ]);

        $response = $this->withHeaders($this->webhookHeaders())
            ->postJson('/webhooks/biteship', [
                'event' => 'order.status',
                'order_id' => $orderId,
                'status' => 'in_transit',
            ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame('in_transit', $transaction->biteship_status);
        $this->assertSame(Transaction::ORDER_SHIPPED, $transaction->status);
    }

    public function test_in_transit_can_enter_return_in_transit(): void
    {
        $orderId = $this->biteshipOrderId('EDGE-RETURN-001');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'in_transit',
        ]);

        $response = $this->withHeaders($this->webhookHeaders())
            ->postJson('/webhooks/biteship', [
                'event' => 'order.status',
                'order_id' => $orderId,
                'status' => 'return_in_transit',
            ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame('return_in_transit', $transaction->biteship_status);
        $this->assertSame(Transaction::ORDER_SHIPPED, $transaction->status);
    }

    public function test_return_in_transit_cannot_become_delivered(): void
    {
        $orderId = $this->biteshipOrderId('EDGE-RETURN-DELIVERED-001');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'shipping_email' => 'customer@example.com',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'return_in_transit',
        ]);

        Mail::fake();

        $response = $this->withHeaders($this->webhookHeaders())
            ->postJson('/webhooks/biteship', [
                'event' => 'order.status',
                'order_id' => $orderId,
                'status' => 'delivered',
            ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame(
            'return_in_transit',
            $transaction->biteship_status
        );

        $this->assertSame(
            Transaction::ORDER_SHIPPED,
            $transaction->status
        );

        Mail::assertNothingSent();
    }

    public function test_return_in_transit_can_become_returned(): void
    {
        $orderId = $this->biteshipOrderId('EDGE-RETURN-002');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'return_in_transit',
        ]);

        $response = $this->withHeaders($this->webhookHeaders())
            ->postJson('/webhooks/biteship', [
                'event' => 'order.status',
                'order_id' => $orderId,
                'status' => 'returned',
            ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame('returned', $transaction->biteship_status);
        $this->assertSame(Transaction::ORDER_SHIPPED, $transaction->status);
    }

    public function test_returned_is_terminal_for_later_progress_status(): void
    {
        $orderId = $this->biteshipOrderId('EDGE-RETURN-003');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'returned',
        ]);

        $response = $this->withHeaders($this->webhookHeaders())
            ->postJson('/webhooks/biteship', [
                'event' => 'order.status',
                'order_id' => $orderId,
                'status' => 'in_transit',
            ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame('returned', $transaction->biteship_status);
        $this->assertSame(Transaction::ORDER_SHIPPED, $transaction->status);
    }

    public function test_rejected_is_terminal_for_later_progress_status(): void
    {
        $orderId = $this->biteshipOrderId('EDGE-REJECTED-001');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'rejected',
        ]);

        $response = $this->withHeaders($this->webhookHeaders())
            ->postJson('/webhooks/biteship', [
                'event' => 'order.status',
                'order_id' => $orderId,
                'status' => 'allocated',
            ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame('rejected', $transaction->biteship_status);
        $this->assertSame(Transaction::ORDER_SHIPPED, $transaction->status);
    }

    public function test_courier_not_found_is_terminal_for_later_progress_status(): void
    {
        $orderId = $this->biteshipOrderId('EDGE-COURIER-001');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'courier_not_found',
        ]);

        $response = $this->withHeaders($this->webhookHeaders())
            ->postJson('/webhooks/biteship', [
                'event' => 'order.status',
                'order_id' => $orderId,
                'status' => 'allocated',
            ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame('courier_not_found', $transaction->biteship_status);
        $this->assertSame(Transaction::ORDER_SHIPPED, $transaction->status);
    }

    public function test_disposed_is_terminal_for_later_delivered_status(): void
    {
        $orderId = $this->biteshipOrderId('EDGE-DISPOSED-001');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'shipping_email' => 'customer@example.com',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'disposed',
        ]);

        Mail::fake();

        $response = $this->withHeaders($this->webhookHeaders())
            ->postJson('/webhooks/biteship', [
                'event' => 'order.status',
                'order_id' => $orderId,
                'status' => 'delivered',
            ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame('disposed', $transaction->biteship_status);
        $this->assertSame(Transaction::ORDER_SHIPPED, $transaction->status);

        Mail::assertNothingSent();
    }

    public function test_in_transit_cannot_regress_to_allocated(): void
    {
        $orderId = $this->biteshipOrderId('EDGE-ORDER-001');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'in_transit',
        ]);

        $response = $this->withHeaders($this->webhookHeaders())
            ->postJson('/webhooks/biteship', [
                'event' => 'order.status',
                'order_id' => $orderId,
                'status' => 'allocated',
            ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame('in_transit', $transaction->biteship_status);
    }

    public function test_dropping_off_cannot_regress_to_picked(): void
    {
        $orderId = $this->biteshipOrderId('EDGE-ORDER-002');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'dropping_off',
        ]);

        $response = $this->withHeaders($this->webhookHeaders())
            ->postJson('/webhooks/biteship', [
                'event' => 'order.status',
                'order_id' => $orderId,
                'status' => 'picked',
            ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame('dropping_off', $transaction->biteship_status);
    }

    public function test_cancelled_cannot_regress_to_old_progress_status(): void
    {
        $orderId = $this->biteshipOrderId('EDGE-ORDER-003');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'cancelled',
        ]);

        $response = $this->withHeaders($this->webhookHeaders())
            ->postJson('/webhooks/biteship', [
                'event' => 'order.status',
                'order_id' => $orderId,
                'status' => 'in_transit',
            ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame('cancelled', $transaction->biteship_status);
    }

    public function test_valid_webhook_updates_biteship_status(): void
    {
        $orderId = $this->biteshipOrderId('LIFECYCLE-001');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'confirmed',
        ]);

        $response = $this->withHeaders(
            $this->webhookHeaders()
        )->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'order_id' => $orderId,
            'status' => 'in_transit',
            'courier_tracking_id' => 'TRACK-001',
            'courier_waybill_id' => 'WAYBILL-001',
            'courier_company' => 'jne',
            'courier_type' => 'reg',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'in_transit',
            ]);

        $transaction->refresh();

        $this->assertSame('in_transit', $transaction->biteship_status);
        $this->assertSame('TRACK-001', $transaction->biteship_tracking_id);
        $this->assertSame('WAYBILL-001', $transaction->biteship_waybill_id);
        $this->assertSame('jne', $transaction->courier);
        $this->assertSame(
            Transaction::ORDER_SHIPPED,
            $transaction->status
        );
    }

    public function test_delivered_webhook_completes_courier_transaction(): void
    {
        Mail::fake();

        $orderId = $this->biteshipOrderId('LIFECYCLE-002');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'shipping_email' => 'customer@example.com',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'dropping_off',
        ]);

        $response = $this->withHeaders(
            $this->webhookHeaders()
        )->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'order_id' => $orderId,
            'status' => 'delivered',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'delivered',
                'transaction_status' => Transaction::ORDER_COMPLETED,
            ]);

        $transaction->refresh();

        $this->assertSame(
            Transaction::ORDER_COMPLETED,
            $transaction->status
        );

        $this->assertSame(
            'delivered',
            $transaction->biteship_status
        );

        Mail::assertSent(OrderCompletedMail::class);
    }

    public function test_duplicate_delivered_webhook_is_idempotent(): void
    {
        Mail::fake();

        $orderId = $this->biteshipOrderId('LIFECYCLE-003');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'shipping_email' => 'customer@example.com',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'dropping_off',
        ]);

        $payload = [
            'event' => 'order.status',
            'order_id' => $orderId,
            'status' => 'delivered',
        ];

        $this->withHeaders($this->webhookHeaders())
            ->postJson('/webhooks/biteship', $payload)
            ->assertOk();

        $this->withHeaders($this->webhookHeaders())
            ->postJson('/webhooks/biteship', $payload)
            ->assertOk();

        $transaction->refresh();

        $this->assertSame(
            Transaction::ORDER_COMPLETED,
            $transaction->status
        );

        $this->assertSame(
            'delivered',
            $transaction->biteship_status
        );

        $this->assertSame(
            1,
            DB::table('order_status_histories')
                ->where('transaction_id', $transaction->id)
                ->where('status', Transaction::ORDER_COMPLETED)
                ->count()
        );

        Mail::assertSent(OrderCompletedMail::class, 1);
    }

    public function test_stale_webhook_cannot_regress_delivered_status(): void
    {
        Mail::fake();

        $orderId = $this->biteshipOrderId('LIFECYCLE-004');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_COMPLETED,
            'shipping_method' => 'Kurir',
            'shipping_email' => 'customer@example.com',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'delivered',
        ]);

        $response = $this->withHeaders(
            $this->webhookHeaders()
        )->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'order_id' => $orderId,
            'status' => 'dropping_off',
        ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame('delivered', $transaction->biteship_status);

        $this->assertSame(
            Transaction::ORDER_COMPLETED,
            $transaction->status
        );
    }

    public function test_stale_progress_webhook_cannot_regress_in_transit_status(): void
    {
        $orderId = $this->biteshipOrderId('LIFECYCLE-005');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'in_transit',
        ]);

        $response = $this->withHeaders(
            $this->webhookHeaders()
        )->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'order_id' => $orderId,
            'status' => 'allocated',
        ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame(
            'in_transit',
            $transaction->biteship_status
        );

        $this->assertSame(
            Transaction::ORDER_SHIPPED,
            $transaction->status
        );
    }

    public function test_stale_progress_webhook_cannot_regress_dropping_off_status(): void
    {
        $orderId = $this->biteshipOrderId('LIFECYCLE-006');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'dropping_off',
        ]);

        $response = $this->withHeaders(
            $this->webhookHeaders()
        )->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'order_id' => $orderId,
            'status' => 'picked',
        ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame(
            'dropping_off',
            $transaction->biteship_status
        );

        $this->assertSame(
            Transaction::ORDER_SHIPPED,
            $transaction->status
        );
    }

    public function test_in_transit_webhook_can_move_to_on_hold(): void
    {
        $orderId = $this->biteshipOrderId('LIFECYCLE-007');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'in_transit',
        ]);

        $response = $this->withHeaders(
            $this->webhookHeaders()
        )->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'order_id' => $orderId,
            'status' => 'on_hold',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'on_hold',
            ]);

        $transaction->refresh();

        $this->assertSame(
            'on_hold',
            $transaction->biteship_status
        );

        $this->assertSame(
            Transaction::ORDER_SHIPPED,
            $transaction->status
        );
    }

    public function test_on_hold_webhook_can_resume_to_in_transit(): void
    {
        $orderId = $this->biteshipOrderId('LIFECYCLE-008');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'on_hold',
        ]);

        $response = $this->withHeaders(
            $this->webhookHeaders()
        )->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'order_id' => $orderId,
            'status' => 'in_transit',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'in_transit',
            ]);

        $transaction->refresh();

        $this->assertSame(
            'in_transit',
            $transaction->biteship_status
        );

        $this->assertSame(
            Transaction::ORDER_SHIPPED,
            $transaction->status
        );
    }

    public function test_in_transit_webhook_can_move_to_return_in_transit(): void
    {
        $orderId = $this->biteshipOrderId('LIFECYCLE-009');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'in_transit',
        ]);

        $response = $this->withHeaders(
            $this->webhookHeaders()
        )->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'order_id' => $orderId,
            'status' => 'return_in_transit',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'return_in_transit',
            ]);

        $transaction->refresh();

        $this->assertSame(
            'return_in_transit',
            $transaction->biteship_status
        );

        $this->assertSame(
            Transaction::ORDER_SHIPPED,
            $transaction->status
        );
    }

    public function test_return_in_transit_webhook_can_move_to_returned_without_completing_transaction(): void
    {
        $orderId = $this->biteshipOrderId('LIFECYCLE-010');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'return_in_transit',
        ]);

        $response = $this->withHeaders(
            $this->webhookHeaders()
        )->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'order_id' => $orderId,
            'status' => 'returned',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'returned',
            ]);

        $transaction->refresh();

        $this->assertSame(
            'returned',
            $transaction->biteship_status
        );

        $this->assertSame(
            Transaction::ORDER_SHIPPED,
            $transaction->status
        );
    }

    public function test_cancelled_webhook_cannot_resume_to_in_transit(): void
    {
        $orderId = $this->biteshipOrderId('LIFECYCLE-011');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'cancelled',
        ]);

        $response = $this->withHeaders(
            $this->webhookHeaders()
        )->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'order_id' => $orderId,
            'status' => 'in_transit',
        ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame(
            'cancelled',
            $transaction->biteship_status
        );

        $this->assertSame(
            Transaction::ORDER_SHIPPED,
            $transaction->status
        );
    }

    public function test_rejected_webhook_cannot_resume_to_in_transit(): void
    {
        $orderId = $this->biteshipOrderId('LIFECYCLE-012');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'rejected',
        ]);

        $response = $this->withHeaders(
            $this->webhookHeaders()
        )->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'order_id' => $orderId,
            'status' => 'in_transit',
        ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame(
            'rejected',
            $transaction->biteship_status
        );

        $this->assertSame(
            Transaction::ORDER_SHIPPED,
            $transaction->status
        );
    }

    public function test_courier_not_found_webhook_cannot_resume_to_allocated(): void
    {
        $orderId = $this->biteshipOrderId('LIFECYCLE-013');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'courier_not_found',
        ]);

        $response = $this->withHeaders(
            $this->webhookHeaders()
        )->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'order_id' => $orderId,
            'status' => 'allocated',
        ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame(
            'courier_not_found',
            $transaction->biteship_status
        );

        $this->assertSame(
            Transaction::ORDER_SHIPPED,
            $transaction->status
        );
    }

    public function test_disposed_webhook_cannot_become_delivered_or_complete_transaction(): void
    {
        Mail::fake();

        $orderId = $this->biteshipOrderId('LIFECYCLE-014');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'shipping_email' => 'customer@example.com',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'disposed',
        ]);

        $response = $this->withHeaders(
            $this->webhookHeaders()
        )->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'order_id' => $orderId,
            'status' => 'delivered',
        ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame(
            'disposed',
            $transaction->biteship_status
        );

        $this->assertSame(
            Transaction::ORDER_SHIPPED,
            $transaction->status
        );

        Mail::assertNotSent(OrderCompletedMail::class);
    }

    public function test_returned_webhook_cannot_become_delivered_or_complete_transaction(): void
    {
        Mail::fake();

        $orderId = $this->biteshipOrderId('LIFECYCLE-015');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'shipping_email' => 'customer@example.com',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'returned',
        ]);

        $response = $this->withHeaders(
            $this->webhookHeaders()
        )->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'order_id' => $orderId,
            'status' => 'delivered',
        ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame(
            'returned',
            $transaction->biteship_status
        );

        $this->assertSame(
            Transaction::ORDER_SHIPPED,
            $transaction->status
        );

        Mail::assertNotSent(OrderCompletedMail::class);
    }

    public function test_cancelled_webhook_only_updates_provider_status(): void
    {
        $orderId = $this->biteshipOrderId('LIFECYCLE-005');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'in_transit',
        ]);

        $response = $this->withHeaders(
            $this->webhookHeaders()
        )->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'order_id' => $orderId,
            'status' => 'cancelled',
        ]);

        $response->assertOk();

        $transaction->refresh();

        $this->assertSame(
            'cancelled',
            $transaction->biteship_status
        );

        $this->assertSame(
            Transaction::ORDER_SHIPPED,
            $transaction->status
        );
    }

    public function test_unknown_event_is_rejected(): void
    {
        $response = $this->withHeaders(
            $this->webhookHeaders()
        )->postJson('/webhooks/biteship', [
            'event' => 'order.price',
            'order_id' => 'BITESHIP-UNKNOWN-EVENT',
            'status' => 'delivered',
        ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_webhook_requires_order_id_and_status(): void
    {
        $response = $this->withHeaders(
            $this->webhookHeaders()
        )->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'status' => 'delivered',
        ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_webhook_rejects_unknown_biteship_order(): void
    {
        $response = $this->withHeaders(
            $this->webhookHeaders()
        )->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'order_id' => 'BITESHIP-DOES-NOT-EXIST',
            'status' => 'in_transit',
        ]);

        $response
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_biteship_webhook_fails_closed_when_signature_protocol_is_not_configured(): void
    {
        config([
            'biteship.webhook.signature_key' => null,
            'biteship.webhook.signature_secret' => null,
        ]);

        $response = $this->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'status' => 'delivered',
            'order_id' => 'TEST-001',
        ]);

        $response
            ->assertStatus(503)
            ->assertJson([
                'success' => false,
                'message' => 'Webhook belum dapat diproses.',
            ]);
    }

    public function test_biteship_webhook_accepts_valid_signature(): void
    {
        $orderId = $this->biteshipOrderId('SIGNATURE-001');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'confirmed',
        ]);

        $response = $this->postJson(
            '/webhooks/biteship',
            [
                'event' => 'order.status',
                'order_id' => $orderId,
                'status' => 'allocated',
            ],
            $this->webhookHeaders()
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Webhook diterima.',
                'status' => 'allocated',
            ]);

        $transaction->refresh();

        $this->assertSame(
            'allocated',
            $transaction->biteship_status
        );
    }

    public function test_biteship_webhook_rejects_invalid_signature(): void
    {
        $orderId = $this->biteshipOrderId('SIGNATURE-002');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'confirmed',
        ]);

        $response = $this->postJson(
            '/webhooks/biteship',
            [
                'event' => 'order.status',
                'order_id' => $orderId,
                'status' => 'allocated',
            ],
            [
                'X-Eazywear-Biteship-Signature' => 'wrong-signature',
            ]
        );

        $response
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Webhook signature tidak valid.',
            ]);

        $transaction->refresh();

        $this->assertSame(
            'confirmed',
            $transaction->biteship_status
        );
    }

    public function test_biteship_webhook_rejects_missing_signature(): void
    {
        $orderId = $this->biteshipOrderId('SIGNATURE-003');

        $transaction = Transaction::factory()->create([
            'status' => Transaction::ORDER_SHIPPED,
            'shipping_method' => 'Kurir',
            'biteship_order_id' => $orderId,
            'biteship_status' => 'confirmed',
        ]);

        $response = $this->postJson(
            '/webhooks/biteship',
            [
                'event' => 'order.status',
                'order_id' => $orderId,
                'status' => 'allocated',
            ]
        );

        $response
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Webhook signature tidak valid.',
            ]);

        $transaction->refresh();

        $this->assertSame(
            'confirmed',
            $transaction->biteship_status
        );
    }
}
