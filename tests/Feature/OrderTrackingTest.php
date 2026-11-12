<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\TransactionFulfillment;
use App\Models\TransactionShipment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use DatabaseTransactions;

    private function createTransaction(array $overrides = []): Transaction
    {
        $customer = Customer::query()->firstOrFail();

        return Transaction::create(array_merge([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-TRACK-' . strtoupper(uniqid()),
            'transaction_date' => now(),
            'payment_method' => 'VA',
            'subtotal' => 200000,
            'discount' => 0,
            'shipping' => 15000,
            'total' => 215000,
            'status' => Transaction::ORDER_PROCESSING,
            'source' => 'Website',
            'shipping_name' => 'Customer Tracking Test',
            'shipping_email' => 'tracking-test@example.com',
            'shipping_phone' => '08123456789',
            'shipping_address' => 'Alamat Tracking Test',
            'shipping_district' => 'Banjarmasin Selatan',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
        ], $overrides));
    }

    private function createFulfillment(
        Transaction $transaction,
        string $groupType
    ): TransactionFulfillment {
        return TransactionFulfillment::create([
            'transaction_id' => $transaction->id,
            'group_type' => $groupType,
            'shipping_method' => 'Kurir',
            'shipping_cost' => 0,
            'shipping_name' => $transaction->shipping_name,
            'shipping_email' => $transaction->shipping_email,
            'shipping_phone' => $transaction->shipping_phone,
            'shipping_address' => $transaction->shipping_address,
            'shipping_district' => $transaction->shipping_district,
            'shipping_city' => $transaction->shipping_city,
            'shipping_province' => $transaction->shipping_province,
            'shipping_postal_code' => $transaction->shipping_postal_code,
            'status' => TransactionFulfillment::STATUS_PENDING,
        ]);
    }

    private function createShipment(
        Transaction $transaction,
        string $type,
        string $trackingNumber,
        int $shippingCost = 7500
    ): TransactionShipment {
        return TransactionShipment::create([
            'transaction_id' => $transaction->id,
            'shipment_type' => $type,
            'shipping_method' => 'Kurir',
            'shipping_cost' => $shippingCost,
            'shipping_name' => $transaction->shipping_name,
            'shipping_email' => $transaction->shipping_email,
            'shipping_phone' => $transaction->shipping_phone,
            'shipping_address' => $transaction->shipping_address,
            'shipping_district' => $transaction->shipping_district,
            'shipping_city' => $transaction->shipping_city,
            'shipping_province' => $transaction->shipping_province,
            'shipping_postal_code' => $transaction->shipping_postal_code,
            'tracking_number' => $trackingNumber,
            'courier' => 'J&T Express',
            'status' => TransactionShipment::STATUS_SHIPPED,
        ]);
    }

    private function searchOrder(Transaction $transaction, ?string $email = null)
    {
        return $this->post(route('orders.tracking.search'), [
            'invoice_number' => $transaction->invoice_number,
            'email' => $email ?? $transaction->shipping_email,
        ]);
    }

    public function test_combined_shipment_is_displayed_as_one_shipment(): void
    {
        $transaction = $this->createTransaction([
            'invoice_number' => 'INV-TRACK-COMBINED-' . strtoupper(uniqid()),
        ]);

        $ready = $this->createFulfillment(
            $transaction,
            TransactionFulfillment::GROUP_READY
        );

        $preOrder = $this->createFulfillment(
            $transaction,
            TransactionFulfillment::GROUP_PRE_ORDER
        );

        $shipment = $this->createShipment(
            $transaction,
            TransactionShipment::TYPE_COMBINED,
            'RESI-COMBINED-001',
            15000
        );

        $shipment->fulfillments()->attach([$ready->id, $preOrder->id]);

        $this->searchOrder($transaction)
            ->assertOk()
            ->assertViewIs('orders.tracking-detail')
            ->assertSee('Pengiriman Gabungan')
            ->assertSee('RESI-COMBINED-001')
            ->assertSee('Rp 215.000');

        $this->assertSame(
            1,
            $transaction->shipments()->count(),
            'Pengiriman gabungan harus memiliki satu shipment.'
        );
    }

    public function test_separate_shipments_show_ready_stock_and_pre_order_individually(): void
    {
        $transaction = $this->createTransaction([
            'invoice_number' => 'INV-TRACK-SEPARATE-' . strtoupper(uniqid()),
        ]);

        $ready = $this->createFulfillment(
            $transaction,
            TransactionFulfillment::GROUP_READY
        );

        $preOrder = $this->createFulfillment(
            $transaction,
            TransactionFulfillment::GROUP_PRE_ORDER
        );

        $readyShipment = $this->createShipment(
            $transaction,
            TransactionShipment::TYPE_SEPARATE,
            'RESI-READY-001'
        );

        $preOrderShipment = $this->createShipment(
            $transaction,
            TransactionShipment::TYPE_SEPARATE,
            'RESI-PO-001'
        );

        $readyShipment->fulfillments()->attach([$ready->id]);
        $preOrderShipment->fulfillments()->attach([$preOrder->id]);

        $this->searchOrder($transaction)
            ->assertOk()
            ->assertViewIs('orders.tracking-detail')
            ->assertSee('Ready Stock')
            ->assertSee('Pre-Order')
            ->assertSee('RESI-READY-001')
            ->assertSee('RESI-PO-001')
            ->assertSee('Rp 215.000');

        $this->assertSame(
            2,
            $transaction->shipments()->count(),
            'Pengiriman terpisah harus memiliki dua shipment.'
        );
    }

    public function test_legacy_transaction_without_shipments_still_displays_shipping_details(): void
    {
        $transaction = $this->createTransaction([
            'invoice_number' => 'INV-TRACK-LEGACY-' . strtoupper(uniqid()),
            'status' => Transaction::ORDER_SHIPPED,
            'tracking_number' => 'RESI-LEGACY-001',
            'courier' => 'J&T Express',
            'shipping' => 10000,
            'total' => 210000,
        ]);

        $this->searchOrder($transaction)
            ->assertOk()
            ->assertViewIs('orders.tracking-detail')
            ->assertSee('RESI-LEGACY-001')
            ->assertSee('Alamat Tracking Test')
            ->assertSee('Rp 210.000');

        $this->assertSame(0, $transaction->shipments()->count());
    }

    public function test_wrong_email_cannot_view_order_details(): void
    {
        $transaction = $this->createTransaction([
            'invoice_number' => 'INV-TRACK-WRONG-EMAIL-' . strtoupper(uniqid()),
        ]);

        $this->searchOrder($transaction, 'wrong-email@example.com')
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                'Pesanan tidak ditemukan. Pastikan nomor invoice dan email sudah benar.'
            );
    }

    public function test_payment_summary_uses_invoice_total_not_shipment_cost_sum(): void
    {
        $transaction = $this->createTransaction([
            'invoice_number' => 'INV-TRACK-TOTAL-' . strtoupper(uniqid()),
            'shipping' => 15000,
            'total' => 215000,
        ]);

        $ready = $this->createFulfillment(
            $transaction,
            TransactionFulfillment::GROUP_READY
        );

        $preOrder = $this->createFulfillment(
            $transaction,
            TransactionFulfillment::GROUP_PRE_ORDER
        );

        $readyShipment = $this->createShipment(
            $transaction,
            TransactionShipment::TYPE_SEPARATE,
            'RESI-TOTAL-READY',
            7500
        );

        $preOrderShipment = $this->createShipment(
            $transaction,
            TransactionShipment::TYPE_SEPARATE,
            'RESI-TOTAL-PO',
            7500
        );

        $readyShipment->fulfillments()->attach([$ready->id]);
        $preOrderShipment->fulfillments()->attach([$preOrder->id]);

        $this->searchOrder($transaction)
            ->assertOk()
            ->assertSee('Rp 215.000');

        $transaction->refresh();

        $this->assertSame(15000, (int) $transaction->shipping);
        $this->assertSame(215000, (int) $transaction->total);
    }
    public function test_customer_tracking_returns_biteship_proof_of_delivery_images(): void
    {
        config([
            'biteship.base_url' => 'https://api.biteship.test',
            'biteship.api_key' => 'test-api-key',
            'biteship.timeout' => 10,
        ]);

        $transaction = $this->createTransaction();

        $shipment = $this->createShipment(
            $transaction,
            TransactionShipment::TYPE_SEPARATE,
            'RESI-PROOF-001'
        );

        $shipment->update([
            'biteship_order_id' => 'order-proof-001',
            'biteship_tracking_id' => 'track-proof-001',
        ]);

        Http::fake([
            'https://api.biteship.test/v1/trackings/track-proof-001' =>
                Http::response([
                    'success' => true,
                    'id' => 'track-proof-001',
                    'waybill_id' => 'RESI-PROOF-001',
                    'status' => 'delivered',
                    'history' => [
                        [
                            'status' => 'delivered',
                            'note' => 'Paket telah diterima.',
                        ],
                    ],
                ], 200),

            'https://api.biteship.test/v1/orders/order-proof-001' =>
                Http::response([
                    'success' => true,
                    'id' => 'order-proof-001',
                    'status' => 'delivered',
                    'courier' => [
                        'history' => [
                            [
                                'status' => 'delivered',
                                'proof_of_delivery_images' => [
                                    'https://images.example.com/proof-1.jpg',
                                    'https://images.example.com/proof-2.jpg',
                                ],
                            ],
                        ],
                    ],
                ], 200),
        ]);

        $this->get(route('orders.tracking.shipment', [
            'invoice' => $transaction->invoice_number,
            'shipment' => $shipment->id,
            'email' => $transaction->shipping_email,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tracking_number', 'RESI-PROOF-001')
            ->assertJsonPath('data.history.0.note', 'Paket telah diterima.')
            ->assertJsonPath('data.proof_images.0', 'https://images.example.com/proof-1.jpg')
            ->assertJsonPath('data.proof_images.1', 'https://images.example.com/proof-2.jpg');
    }

    public function test_customer_tracking_still_works_when_biteship_order_request_fails(): void
    {
        config([
            'biteship.base_url' => 'https://api.biteship.test',
            'biteship.api_key' => 'test-api-key',
            'biteship.timeout' => 10,
        ]);

        $transaction = $this->createTransaction();

        $shipment = $this->createShipment(
            $transaction,
            TransactionShipment::TYPE_SEPARATE,
            'RESI-PROOF-002'
        );

        $shipment->update([
            'biteship_order_id' => 'order-proof-002',
            'biteship_tracking_id' => 'track-proof-002',
        ]);

        Http::fake([
            'https://api.biteship.test/v1/trackings/track-proof-002' =>
                Http::response([
                    'success' => true,
                    'id' => 'track-proof-002',
                    'waybill_id' => 'RESI-PROOF-002',
                    'status' => 'in_transit',
                    'history' => [
                        [
                            'status' => 'in_transit',
                            'note' => 'Paket sedang dalam perjalanan.',
                        ],
                    ],
                ], 200),

            'https://api.biteship.test/v1/orders/order-proof-002' =>
                Http::response([
                    'success' => false,
                    'message' => 'Order temporarily unavailable.',
                ], 500),
        ]);

        $this->get(route('orders.tracking.shipment', [
            'invoice' => $transaction->invoice_number,
            'shipment' => $shipment->id,
            'email' => $transaction->shipping_email,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.history.0.note', 'Paket sedang dalam perjalanan.')
            ->assertJsonPath('data.proof_images', []);
    }

    public function test_customer_live_tracking_is_returned_for_gojek(): void
    {
        $this->fakeBiteshipTracking(
            'track-gojek',
            'order-gojek',
            'gojek',
            'https://track.example.com/gojek'
        );

        $transaction = $this->createTransaction();
        $shipment = $this->createShipment(
            $transaction,
            TransactionShipment::TYPE_SEPARATE,
            'RESI-GOJEK'
        );

        $shipment->update([
            'courier' => 'Gojek',
            'courier_code' => ' GOJEK ',
            'biteship_order_id' => 'order-gojek',
            'biteship_tracking_id' => 'track-gojek',
        ]);

        $this->get(route('orders.tracking.shipment', [
            'invoice' => $transaction->invoice_number,
            'shipment' => $shipment->id,
            'email' => $transaction->shipping_email,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.courier_code', 'gojek')
            ->assertJsonPath('data.live_tracking_allowed', true)
            ->assertJsonPath(
                'data.live_tracking_url',
                'https://track.example.com/gojek'
            );
    }

    public static function nonGojekCourierProvider(): array
    {
        return [
            'jnt' => [' JnT ', 'jnt'],
            'lion parcel' => ['Lion Parcel', 'lion parcel'],
            'other courier' => ['jne', 'jne'],
            'unknown courier' => [null, null],
        ];
    }

    #[DataProvider('nonGojekCourierProvider')]
    public function test_customer_live_tracking_is_hidden_for_non_gojek(
        ?string $storedCourier,
        ?string $expectedCourierCode
    ): void {
        $this->fakeBiteshipTracking(
            'track-non-gojek',
            'order-non-gojek',
            null,
            'https://track.example.com/non-gojek'
        );

        $transaction = $this->createTransaction();
        $shipment = $this->createShipment(
            $transaction,
            TransactionShipment::TYPE_SEPARATE,
            'RESI-NON-GOJEK'
        );

        $shipment->update([
            'courier' => null,
            'courier_code' => $storedCourier,
            'biteship_order_id' => 'order-non-gojek',
            'biteship_tracking_id' => 'track-non-gojek',
        ]);

        $this->get(route('orders.tracking.shipment', [
            'invoice' => $transaction->invoice_number,
            'shipment' => $shipment->id,
            'email' => $transaction->shipping_email,
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.courier_code', $expectedCourierCode)
            ->assertJsonPath('data.live_tracking_allowed', false)
            ->assertJsonPath('data.live_tracking_url', null);
    }

    public function test_customer_live_tracking_is_hidden_for_gojek_without_https_url(): void
    {
        $this->fakeBiteshipTracking(
            'track-gojek-no-link',
            'order-gojek-no-link',
            'gojek',
            'http://track.example.com/insecure'
        );

        $transaction = $this->createTransaction();
        $shipment = $this->createShipment(
            $transaction,
            TransactionShipment::TYPE_SEPARATE,
            'RESI-GOJEK-NO-LINK'
        );

        $shipment->update([
            'courier' => 'Gojek',
            'courier_code' => 'gojek',
            'biteship_order_id' => 'order-gojek-no-link',
            'biteship_tracking_id' => 'track-gojek-no-link',
        ]);

        $this->get(route('orders.tracking.shipment', [
            'invoice' => $transaction->invoice_number,
            'shipment' => $shipment->id,
            'email' => $transaction->shipping_email,
        ]))
            ->assertOk()
            ->assertJsonPath('data.live_tracking_allowed', true)
            ->assertJsonPath('data.live_tracking_url', null);
    }

    public function test_customer_live_tracking_follows_each_shipment_courier(): void
    {
        config([
            'biteship.base_url' => 'https://api.biteship.test',
            'biteship.api_key' => 'test-api-key',
            'biteship.timeout' => 10,
        ]);

        Http::fake([
            'https://api.biteship.test/v1/trackings/track-gojek' =>
                Http::response([
                    'success' => true,
                    'id' => 'track-gojek',
                    'waybill_id' => 'RESI-GOJEK',
                    'status' => 'in_transit',
                    'courier' => ['company' => 'gojek'],
                    'history' => [],
                ], 200),

            'https://api.biteship.test/v1/orders/order-gojek' =>
                Http::response([
                    'success' => true,
                    'id' => 'order-gojek',
                    'courier' => [
                        'link' => 'https://track.example.com/gojek',
                        'history' => [],
                    ],
                ], 200),

            'https://api.biteship.test/v1/trackings/track-jnt' =>
                Http::response([
                    'success' => true,
                    'id' => 'track-jnt',
                    'waybill_id' => 'RESI-JNT',
                    'status' => 'in_transit',
                    'courier' => ['company' => 'jnt'],
                    'history' => [],
                ], 200),

            'https://api.biteship.test/v1/orders/order-jnt' =>
                Http::response([
                    'success' => true,
                    'id' => 'order-jnt',
                    'courier' => [
                        'link' => 'https://track.example.com/jnt',
                        'history' => [],
                    ],
                ], 200),
        ]);

        $transaction = $this->createTransaction();

        $gojekShipment = $this->createShipment(
            $transaction,
            TransactionShipment::TYPE_SEPARATE,
            'RESI-GOJEK'
        );
        $gojekShipment->update([
            'courier' => 'Gojek',
            'courier_code' => 'gojek',
            'biteship_order_id' => 'order-gojek',
            'biteship_tracking_id' => 'track-gojek',
        ]);

        $jntShipment = $this->createShipment(
            $transaction,
            TransactionShipment::TYPE_SEPARATE,
            'RESI-JNT'
        );
        $jntShipment->update([
            'courier' => 'J&T Express',
            'courier_code' => 'jnt',
            'biteship_order_id' => 'order-jnt',
            'biteship_tracking_id' => 'track-jnt',
        ]);

        $this->get(route('orders.tracking.shipment', [
            'invoice' => $transaction->invoice_number,
            'shipment' => $gojekShipment->id,
            'email' => $transaction->shipping_email,
        ]))
            ->assertOk()
            ->assertJsonPath('data.live_tracking_allowed', true)
            ->assertJsonPath(
                'data.live_tracking_url',
                'https://track.example.com/gojek'
            );

        $this->get(route('orders.tracking.shipment', [
            'invoice' => $transaction->invoice_number,
            'shipment' => $jntShipment->id,
            'email' => $transaction->shipping_email,
        ]))
            ->assertOk()
            ->assertJsonPath('data.live_tracking_allowed', false)
            ->assertJsonPath('data.live_tracking_url', null);
    }

    private function fakeBiteshipTracking(
        string $trackingId,
        string $orderId,
        ?string $courierCode,
        ?string $liveTrackingUrl
    ): void {
        config([
            'biteship.base_url' => 'https://api.biteship.test',
            'biteship.api_key' => 'test-api-key',
            'biteship.timeout' => 10,
        ]);

        Http::fake([
            "https://api.biteship.test/v1/trackings/{$trackingId}" =>
                Http::response([
                    'success' => true,
                    'id' => $trackingId,
                    'waybill_id' => 'RESI-' . strtoupper($trackingId),
                    'status' => 'in_transit',
                    'courier' => ['company' => $courierCode],
                    'history' => [],
                ], 200),

            "https://api.biteship.test/v1/orders/{$orderId}" =>
                Http::response([
                    'success' => true,
                    'id' => $orderId,
                    'courier' => [
                        'link' => $liveTrackingUrl,
                        'history' => [],
                    ],
                ], 200),
        ]);
    }
}
