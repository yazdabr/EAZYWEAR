<?php

namespace Tests\Feature;

use App\Mail\OrderShippedMail;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\TransactionShipment;
use App\Models\TransactionItem;
use App\Models\TransactionFulfillment;
use App\Models\ProductVariant;
use App\Services\BiteshipService;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TransactionShippingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_ship_creates_biteship_shipment_and_marks_transaction_shipped(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $this->actingAs($user);

        $customer = Customer::query()->firstOrFail();

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-BITESHIP-E2E-001',
            'transaction_date' => now(),
            'payment_method' => 'VA',
            'subtotal' => 100000,
            'discount' => 0,
            'shipping' => 8000,
            'total' => 108000,
            'status' => Transaction::ORDER_PROCESSING,
            'source' => 'Website',

            'shipping_name' => 'Customer Biteship Test',
            'shipping_email' => 'biteship-e2e@test.com',
            'shipping_phone' => '08123456789',
            'shipping_address' => 'Alamat Biteship Test',
            'shipping_district' => 'Banjarmasin Selatan',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',

            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
        ]);

        $biteship = $this->mock(BiteshipService::class);

        $biteship
            ->shouldReceive('createOrder')
            ->once()
            ->withArgs(function (array $payload) use ($transaction) {
                return $payload['reference_id'] === $transaction->invoice_number
                    && $payload['destination_contact_name']
                        === $transaction->shipping_name
                    && $payload['destination_contact_phone']
                        === $transaction->shipping_phone
                    && $payload['destination_address']
                        === $transaction->shipping_address
                    && $payload['destination_postal_code']
                        === $transaction->shipping_postal_code
                    && $payload['courier_company'] === 'jnt'
                    && $payload['courier_type'] === 'ez';
            })
            ->andReturn([
                'success' => true,
                'order_id' => 'BTS-TEST-ORDER-001',
                'tracking_id' => 'BTS-TRACK-001',
                'waybill_id' => 'BTS-WAYBILL-001',
                'status' => 'confirmed',
                'reference_id' => $transaction->invoice_number,
                'courier_code' => 'jnt',
                'courier_type' => 'ez',
            ]);

        $response = $this->patch(
            route('admin.transactions.ship', $transaction)
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $transaction->id,
                    'status' => Transaction::ORDER_PROCESSING,
                    'biteship_order_id' => 'BTS-TEST-ORDER-001',
                    'biteship_tracking_id' => 'BTS-TRACK-001',
                    'biteship_waybill_id' => 'BTS-WAYBILL-001',
                    'biteship_status' => 'confirmed',
                ],
            ]);

        $transaction->refresh();

        $this->assertSame(
            Transaction::ORDER_PROCESSING,
            $transaction->status
        );

        $this->assertSame(
            'BTS-TEST-ORDER-001',
            $transaction->biteship_order_id
        );

        $this->assertSame(
            'BTS-TRACK-001',
            $transaction->biteship_tracking_id
        );

        $this->assertSame(
            'BTS-WAYBILL-001',
            $transaction->biteship_waybill_id
        );

        $this->assertSame(
            'confirmed',
            $transaction->biteship_status
        );

        $this->assertSame(
            'BTS-WAYBILL-001',
            $transaction->tracking_number
        );

        $this->assertSame(
            'jnt',
            $transaction->courier
        );

        Mail::assertNotSent(OrderShippedMail::class);

        $this->assertDatabaseMissing('transaction_notifications', [
            'transaction_id' => $transaction->id,
            'type' => 'ORDER_SHIPPED_EMAIL',
        ]);
    }

    public function test_admin_ship_does_not_mark_transaction_shipped_when_biteship_fails(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $this->actingAs($user);

        $customer = Customer::query()->firstOrFail();

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-BITESHIP-FAIL-001',
            'transaction_date' => now(),
            'payment_method' => 'VA',
            'subtotal' => 100000,
            'discount' => 0,
            'shipping' => 8000,
            'total' => 108000,
            'status' => Transaction::ORDER_PROCESSING,
            'source' => 'Website',

            'shipping_name' => 'Customer Biteship Failure Test',
            'shipping_email' => 'biteship-failure@test.com',
            'shipping_phone' => '08123456789',
            'shipping_address' => 'Alamat Biteship Failure Test',
            'shipping_district' => 'Banjarmasin Selatan',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',

            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
        ]);

        $biteship = $this->mock(BiteshipService::class);

        $biteship
            ->shouldReceive('createOrder')
            ->once()
            ->andThrow(new \RuntimeException(
                'Biteship API gagal membuat shipment.'
            ));

        $response = $this->patch(
            route('admin.transactions.ship', $transaction)
        );

        $response->assertStatus(500);

        $transaction->refresh();

        $this->assertSame(
            Transaction::ORDER_PROCESSING,
            $transaction->status
        );

        $this->assertNull($transaction->biteship_order_id);
        $this->assertNull($transaction->biteship_tracking_id);
        $this->assertNull($transaction->biteship_waybill_id);
        $this->assertNull($transaction->biteship_status);
        $this->assertNull($transaction->tracking_number);

        Mail::assertNothingSent();

        $this->assertDatabaseMissing('transaction_notifications', [
            'transaction_id' => $transaction->id,
            'type' => 'ORDER_SHIPPED_EMAIL',
        ]);
    }

    public function test_admin_ship_reuses_existing_biteship_order_instead_of_creating_duplicate(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $this->actingAs($user);

        $customer = Customer::query()->firstOrFail();

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-BITESHIP-IDEMPOTENCY-001',
            'transaction_date' => now(),
            'payment_method' => 'VA',
            'subtotal' => 100000,
            'discount' => 0,
            'shipping' => 8000,
            'total' => 108000,
            'status' => Transaction::ORDER_PROCESSING,
            'source' => 'Website',

            'shipping_name' => 'Customer Biteship Idempotency Test',
            'shipping_email' => 'biteship-idempotency@test.com',
            'shipping_phone' => '08123456789',
            'shipping_address' => 'Alamat Biteship Idempotency Test',
            'shipping_district' => 'Banjarmasin Selatan',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',

            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',

            'biteship_order_id' => 'BTS-EXISTING-ORDER-001',
        ]);

        $biteship = $this->mock(BiteshipService::class);

        $biteship
            ->shouldReceive('createOrder')
            ->never();

        $biteship
            ->shouldReceive('getOrder')
            ->once()
            ->with('BTS-EXISTING-ORDER-001')
            ->andReturn([
                'success' => true,
                'order_id' => 'BTS-EXISTING-ORDER-001',
                'tracking_id' => 'BTS-EXISTING-TRACK-001',
                'waybill_id' => 'BTS-EXISTING-WAYBILL-001',
                'status' => 'confirmed',
                'reference_id' => $transaction->invoice_number,
                'courier_code' => 'jnt',
                'courier_type' => 'ez',
            ]);

        $response = $this->patch(
            route('admin.transactions.ship', $transaction)
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $transaction->id,
                    'status' => Transaction::ORDER_PROCESSING,
                    'biteship_order_id' => 'BTS-EXISTING-ORDER-001',
                    'biteship_tracking_id' => 'BTS-EXISTING-TRACK-001',
                    'biteship_waybill_id' => 'BTS-EXISTING-WAYBILL-001',
                    'biteship_status' => 'confirmed',
                ],
            ]);

        $transaction->refresh();

        $this->assertSame(
            Transaction::ORDER_PROCESSING,
            $transaction->status
        );

        $this->assertSame(
            'BTS-EXISTING-ORDER-001',
            $transaction->biteship_order_id
        );

        $this->assertSame(
            'BTS-EXISTING-TRACK-001',
            $transaction->biteship_tracking_id
        );

        $this->assertSame(
            'BTS-EXISTING-WAYBILL-001',
            $transaction->biteship_waybill_id
        );

        $this->assertSame(
            'confirmed',
            $transaction->biteship_status
        );

        $this->assertSame(
            'BTS-EXISTING-WAYBILL-001',
            $transaction->tracking_number
        );

        Mail::assertNotSent(OrderShippedMail::class);

        $this->assertDatabaseMissing('transaction_notifications', [
            'transaction_id' => $transaction->id,
            'type' => 'ORDER_SHIPPED_EMAIL',
        ]);
    }
    public function test_admin_ship_uses_manual_tracking_when_biteship_courier_is_unavailable(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $this->actingAs($user);

        $customer = Customer::query()->firstOrFail();

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-MANUAL-TRACKING-001',
            'transaction_date' => now(),
            'payment_method' => 'VA',
            'subtotal' => 100000,
            'discount' => 0,
            'shipping' => 8000,
            'total' => 108000,
            'status' => Transaction::ORDER_PROCESSING,
            'source' => 'Website',

            'shipping_name' => 'Customer Manual Tracking Test',
            'shipping_email' => 'manual-tracking@test.com',
            'shipping_phone' => '08123456789',
            'shipping_address' => 'Alamat Manual Tracking Test',
            'shipping_district' => 'Banjarmasin Selatan',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',

            'courier_code' => null,
            'courier_service_code' => null,
        ]);

        $biteship = $this->mock(BiteshipService::class);

        $biteship
            ->shouldReceive('createOrder')
            ->never();

        $response = $this->patch(
            route('admin.transactions.ship', $transaction),
            [
                'tracking_number' => 'MANUAL-TRACK-001',
            ]
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $transaction->id,
                    'status' => Transaction::ORDER_SHIPPED,
                    'tracking_number' => 'MANUAL-TRACK-001',
                ],
            ]);

        $transaction->refresh();

        $this->assertSame(
            Transaction::ORDER_SHIPPED,
            $transaction->status
        );

        $this->assertSame(
            'MANUAL-TRACK-001',
            $transaction->tracking_number
        );

        $this->assertNull($transaction->biteship_order_id);
        $this->assertNull($transaction->biteship_tracking_id);
        $this->assertNull($transaction->biteship_waybill_id);
        $this->assertNull($transaction->biteship_status);

        Mail::assertSent(
            OrderShippedMail::class,
            function (OrderShippedMail $mail) use ($transaction) {
                return $mail->transaction->id === $transaction->id;
            }
        );

        $this->assertDatabaseHas('transaction_notifications', [
            'transaction_id' => $transaction->id,
            'type' => 'ORDER_SHIPPED_EMAIL',
        ]);
    }
    public function test_shipping_lock_prevents_duplicate_shipping_process(): void
    {
        $transaction = Transaction::create([
            'invoice_number' => 'INV-SHIP-LOCK-001',
            'transaction_date' => now(),
            'payment_method' => 'VA',
            'subtotal' => 100000,
            'discount' => 0,
            'shipping' => 8000,
            'total' => 108000,
            'status' => Transaction::ORDER_PROCESSING,
            'source' => 'Website',

            'shipping_name' => 'Customer Shipping Lock Test',
            'shipping_email' => 'shipping-lock@test.com',
            'shipping_phone' => '08123456789',
            'shipping_address' => 'Alamat Shipping Lock Test',
            'shipping_district' => 'Banjarmasin Selatan',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
        ]);

        $lock = Cache::lock(
            'biteship:ship:' . $transaction->id,
            120
        );

        $this->assertTrue(
            $lock->get(),
            'Lock pertama seharusnya berhasil didapatkan.'
        );

        try {
            $secondLock = Cache::lock(
                'biteship:ship:' . $transaction->id,
                120
            );

            $this->assertFalse(
                $secondLock->get(),
                'Lock kedua tidak boleh didapatkan selama lock pertama masih aktif.'
            );
        } finally {
            optional($lock)->release();
        }
    }

    public function test_admin_ship_shipment_delivered_completes_shipment_and_all_fulfillments(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($user);

        $customer = Customer::query()->firstOrFail();
        $variant = ProductVariant::query()->with('product')->firstOrFail();

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-COMBINED-DELIVERED-TEST',
            'transaction_date' => now(),
            'payment_method' => 'VA',
            'subtotal' => 200000,
            'discount' => 0,
            'shipping' => 15000,
            'total' => 215000,
            'status' => Transaction::ORDER_PROCESSING,
            'source' => 'Website',
            'fulfillment_mode' => Transaction::FULFILLMENT_MODE_COMBINED,
            'shipping_name' => 'Customer Delivered Test',
            'shipping_email' => 'combined-delivered@test.com',
            'shipping_phone' => '08123456789',
            'shipping_address' => 'Alamat Delivered Test',
            'shipping_district' => 'Banjarmasin Selatan',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
        ]);

        $groups = collect([
            TransactionFulfillment::GROUP_READY,
            TransactionFulfillment::GROUP_PRE_ORDER,
        ])->map(function (string $type) use ($transaction, $variant) {
            $group = TransactionFulfillment::create([
                'transaction_id' => $transaction->id,
                'group_type' => $type,
                'shipping_method' => 'Kurir',
                'shipping_cost' => 0,
                'shipping_name' => $transaction->shipping_name,
                'shipping_email' => $transaction->shipping_email,
                'shipping_phone' => $transaction->shipping_phone,
                'shipping_address' => $transaction->shipping_address,
                'shipping_city' => $transaction->shipping_city,
                'shipping_province' => $transaction->shipping_province,
                'shipping_postal_code' => $transaction->shipping_postal_code,
                'courier_code' => 'jnt',
                'courier_service_code' => 'ez',
                'status' => TransactionFulfillment::STATUS_PENDING,
            ]);

            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'transaction_fulfillment_id' => $group->id,
                'product_variant_id' => $variant->id,
                'qty' => 1,
                'price' => 100000,
                'subtotal' => 100000,
                'weight' => 500,
            ]);

            return $group;
        });

        $shipment = TransactionShipment::create([
            'transaction_id' => $transaction->id,
            'shipment_type' => TransactionShipment::TYPE_COMBINED,
            'shipping_method' => 'Kurir',
            'shipping_cost' => 15000,
            'shipping_name' => $transaction->shipping_name,
            'shipping_email' => $transaction->shipping_email,
            'shipping_phone' => $transaction->shipping_phone,
            'shipping_address' => $transaction->shipping_address,
            'shipping_district' => $transaction->shipping_district,
            'shipping_city' => $transaction->shipping_city,
            'shipping_province' => $transaction->shipping_province,
            'shipping_postal_code' => $transaction->shipping_postal_code,
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
            'status' => TransactionShipment::STATUS_PENDING,
        ]);

        $shipment->fulfillments()->attach($groups->pluck('id')->all());

        $this->mock(BiteshipService::class)
            ->shouldReceive('createOrder')
            ->once()
            ->andReturn([
                'success' => true,
                'order_id' => 'BTS-COMBINED-DELIVERED-001',
                'tracking_id' => 'BTS-COMBINED-DELIVERED-TRACK',
                'waybill_id' => 'BTS-COMBINED-DELIVERED-WAYBILL',
                'status' => 'delivered',
                'reference_id' => $transaction->invoice_number . '-SHIPMENT-' . $shipment->id,
                'courier_code' => 'jnt',
                'courier_type' => 'ez',
            ]);

        $response = $this->patch(route('admin.transactions.shipments.ship', [
            'transaction' => $transaction->id,
            'shipment' => $shipment->id,
        ]));

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertSame(
            TransactionShipment::STATUS_COMPLETED,
            $shipment->fresh()->status
        );

        foreach ($groups as $group) {
            $this->assertSame(
                'delivered',
                $group->fresh()->biteship_status
            );
            $this->assertSame(
                TransactionFulfillment::STATUS_COMPLETED,
                $group->fresh()->status
            );
        }

        $this->assertSame(
            Transaction::ORDER_COMPLETED,
            $transaction->fresh()->status
        );

        $this->assertDatabaseHas('transaction_notifications', [
            'transaction_id' => $transaction->id,
            'type' => 'ORDER_COMPLETED_EMAIL',
        ]);
    }
    public function test_admin_ship_creates_one_biteship_order_for_combined_fulfillments(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $this->actingAs($user);

        $customer = Customer::query()->firstOrFail();
        $variant = ProductVariant::query()->with('product')->firstOrFail();

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-COMBINED-SHIPMENT-001',
            'transaction_date' => now(),
            'payment_method' => 'VA',
            'subtotal' => 200000,
            'discount' => 0,
            'shipping' => 15000,
            'total' => 215000,
            'status' => Transaction::ORDER_PROCESSING,
            'source' => 'Website',
            'fulfillment_mode' => Transaction::FULFILLMENT_MODE_COMBINED,
            'shipping_name' => 'Customer Combined Test',
            'shipping_email' => 'combined-shipment@test.com',
            'shipping_phone' => '08123456789',
            'shipping_address' => 'Alamat Combined Test',
            'shipping_district' => 'Banjarmasin Selatan',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
        ]);

        $ready = TransactionFulfillment::create([
            'transaction_id' => $transaction->id,
            'group_type' => TransactionFulfillment::GROUP_READY,
            'shipping_method' => 'Kurir',
            'shipping_cost' => 0,
            'shipping_name' => $transaction->shipping_name,
            'shipping_email' => $transaction->shipping_email,
            'shipping_phone' => $transaction->shipping_phone,
            'shipping_address' => $transaction->shipping_address,
            'shipping_city' => $transaction->shipping_city,
            'shipping_province' => $transaction->shipping_province,
            'shipping_postal_code' => $transaction->shipping_postal_code,
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
            'status' => TransactionFulfillment::STATUS_PENDING,
        ]);

        $preOrder = TransactionFulfillment::create([
            'transaction_id' => $transaction->id,
            'group_type' => TransactionFulfillment::GROUP_PRE_ORDER,
            'shipping_method' => 'Kurir',
            'shipping_cost' => 0,
            'shipping_name' => $transaction->shipping_name,
            'shipping_email' => $transaction->shipping_email,
            'shipping_phone' => $transaction->shipping_phone,
            'shipping_address' => $transaction->shipping_address,
            'shipping_city' => $transaction->shipping_city,
            'shipping_province' => $transaction->shipping_province,
            'shipping_postal_code' => $transaction->shipping_postal_code,
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
            'status' => TransactionFulfillment::STATUS_PENDING,
        ]);

        foreach ([$ready, $preOrder] as $fulfillment) {
            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'transaction_fulfillment_id' => $fulfillment->id,
                'product_variant_id' => $variant->id,
                'qty' => 1,
                'price' => 100000,
                'subtotal' => 100000,
                'weight' => 500,
            ]);
        }

        $shipment = TransactionShipment::create([
            'transaction_id' => $transaction->id,
            'shipment_type' => TransactionShipment::TYPE_COMBINED,
            'shipping_method' => 'Kurir',
            'shipping_cost' => 15000,
            'shipping_name' => $transaction->shipping_name,
            'shipping_email' => $transaction->shipping_email,
            'shipping_phone' => $transaction->shipping_phone,
            'shipping_address' => $transaction->shipping_address,
            'shipping_district' => $transaction->shipping_district,
            'shipping_city' => $transaction->shipping_city,
            'shipping_province' => $transaction->shipping_province,
            'shipping_postal_code' => $transaction->shipping_postal_code,
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
            'status' => TransactionShipment::STATUS_PENDING,
        ]);

        $shipment->fulfillments()->attach([$ready->id, $preOrder->id]);

        $biteship = $this->mock(BiteshipService::class);

        $biteship
            ->shouldReceive('createOrder')
            ->once()
            ->withArgs(function (array $payload) use ($transaction, $variant) {
                $items = $payload['items'] ?? [];

                return $payload['reference_id']
                        === $transaction->invoice_number . '-SHIPMENT-' . $transaction->shipments()->first()->id
                    && count($items) === 2
                    && collect($items)->sum('quantity') === 2
                    && collect($items)->every(
                        fn (array $item) => ($item['value'] ?? null) === 100000
                    )
                    && $payload['destination_address'] === $transaction->shipping_address
                    && $payload['courier_company'] === 'jnt'
                    && $payload['courier_type'] === 'ez';
            })
            ->andReturn([
                'success' => true,
                'order_id' => 'BTS-COMBINED-TEST-001',
                'tracking_id' => 'BTS-COMBINED-TRACK-001',
                'waybill_id' => 'BTS-COMBINED-WAYBILL-001',
                'status' => 'confirmed',
                'reference_id' => $transaction->invoice_number . '-SHIPMENT-' . $shipment->id,
                'courier_code' => 'jnt',
                'courier_type' => 'ez',
            ]);

        $response = $this->patch(
            route('admin.transactions.shipments.ship', [
                'transaction' => $transaction->id,
                'shipment' => $shipment->id,
            ])
        );

        $response->assertOk()->assertJson([
            'success' => true,
        ]);

        $shipment->refresh();
        $ready->refresh();
        $preOrder->refresh();

        $this->assertSame('BTS-COMBINED-TEST-001', $shipment->biteship_order_id);
        $this->assertSame('BTS-COMBINED-TRACK-001', $shipment->biteship_tracking_id);
        $this->assertSame('BTS-COMBINED-WAYBILL-001', $shipment->biteship_waybill_id);
        $this->assertSame(TransactionFulfillment::STATUS_PROCESSING, $ready->status);
        $this->assertSame(TransactionFulfillment::STATUS_PROCESSING, $preOrder->status);

        $this->assertNull($ready->biteship_order_id);
        $this->assertNull($preOrder->biteship_order_id);

        $this->assertDatabaseCount('transaction_shipments', 1);
        $this->assertDatabaseCount('transaction_shipment_fulfillment', 2);
    }

    public function test_admin_ship_completes_legacy_transaction_when_biteship_returns_delivered(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($user);

        $customer = Customer::query()->firstOrFail();

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-LEGACY-DELIVERED-TEST',
            'transaction_date' => now(),
            'payment_method' => 'VA',
            'subtotal' => 100000,
            'discount' => 0,
            'shipping' => 8000,
            'total' => 108000,
            'status' => Transaction::ORDER_PROCESSING,
            'source' => 'Website',
            'shipping_name' => 'Legacy Delivered Test',
            'shipping_email' => 'legacy-delivered@test.com',
            'shipping_phone' => '08123456789',
            'shipping_address' => 'Alamat Legacy Delivered',
            'shipping_district' => 'Banjarmasin Selatan',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
        ]);

        $this->mock(BiteshipService::class)
            ->shouldReceive('createOrder')
            ->once()
            ->andReturn([
                'success' => true,
                'order_id' => 'BTS-LEGACY-DELIVERED',
                'tracking_id' => 'TRACK-LEGACY-DELIVERED',
                'waybill_id' => 'WAYBILL-LEGACY-DELIVERED',
                'status' => 'delivered',
                'reference_id' => $transaction->invoice_number,
                'courier_code' => 'jnt',
                'courier_type' => 'ez',
            ]);

        $response = $this->patch(
            route('admin.transactions.ship', $transaction)
        );

        $response->assertOk()->assertJson([
            'success' => true,
            'data' => [
                'status' => Transaction::ORDER_COMPLETED,
                'biteship_status' => 'delivered',
            ],
        ]);

        $this->assertSame(
            Transaction::ORDER_COMPLETED,
            $transaction->fresh()->status
        );

        $this->assertDatabaseHas('transaction_notifications', [
            'transaction_id' => $transaction->id,
            'type' => 'ORDER_COMPLETED_EMAIL',
        ]);

        $this->assertDatabaseMissing('transaction_notifications', [
            'transaction_id' => $transaction->id,
            'type' => 'ORDER_SHIPPED_EMAIL',
        ]);
    }

    public function test_admin_ship_fulfillment_completes_group_when_biteship_returns_delivered(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($user);

        $customer = Customer::query()->firstOrFail();
        $variant = ProductVariant::query()->with('product')->firstOrFail();

        $transaction = Transaction::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-FULFILLMENT-DELIVERED-TEST',
            'transaction_date' => now(),
            'payment_method' => 'VA',
            'subtotal' => 100000,
            'discount' => 0,
            'shipping' => 8000,
            'total' => 108000,
            'status' => Transaction::ORDER_PROCESSING,
            'source' => 'Website',
            'fulfillment_mode' => Transaction::FULFILLMENT_MODE_COMBINED,
            'shipping_name' => 'Fulfillment Delivered Test',
            'shipping_email' => 'fulfillment-delivered@test.com',
            'shipping_phone' => '08123456789',
            'shipping_address' => 'Alamat Fulfillment Delivered',
            'shipping_district' => 'Banjarmasin Selatan',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
        ]);

        $group = TransactionFulfillment::create([
            'transaction_id' => $transaction->id,
            'group_type' => TransactionFulfillment::GROUP_READY,
            'shipping_method' => 'Kurir',
            'shipping_cost' => 8000,
            'shipping_name' => $transaction->shipping_name,
            'shipping_email' => $transaction->shipping_email,
            'shipping_phone' => $transaction->shipping_phone,
            'shipping_address' => $transaction->shipping_address,
            'shipping_city' => $transaction->shipping_city,
            'shipping_province' => $transaction->shipping_province,
            'shipping_postal_code' => $transaction->shipping_postal_code,
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
            'status' => TransactionFulfillment::STATUS_PENDING,
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'transaction_fulfillment_id' => $group->id,
            'product_variant_id' => $variant->id,
            'qty' => 1,
            'price' => 100000,
            'subtotal' => 100000,
            'weight' => 500,
        ]);

        $this->mock(BiteshipService::class)
            ->shouldReceive('createOrder')
            ->once()
            ->andReturn([
                'success' => true,
                'order_id' => 'BTS-FULFILLMENT-DELIVERED',
                'tracking_id' => 'TRACK-FULFILLMENT-DELIVERED',
                'waybill_id' => 'WAYBILL-FULFILLMENT-DELIVERED',
                'status' => 'delivered',
                'reference_id' => $transaction->invoice_number . '-READY',
                'courier_code' => 'jnt',
                'courier_type' => 'ez',
            ]);

        $response = $this->patch(route('admin.transactions.fulfillments.ship', [
            'transaction' => $transaction->id,
            'fulfillment' => $group->id,
        ]));

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertSame(
            'delivered',
            $group->fresh()->biteship_status
        );

        $this->assertSame(
            TransactionFulfillment::STATUS_COMPLETED,
            $group->fresh()->status
        );

        $this->assertSame(
            Transaction::ORDER_COMPLETED,
            $transaction->fresh()->status
        );

        $this->assertDatabaseHas('transaction_notifications', [
            'transaction_id' => $transaction->id,
            'type' => 'ORDER_COMPLETED_EMAIL',
        ]);
    }
}
