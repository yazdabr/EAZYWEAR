<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\TransactionShipment;
use App\Models\User;
use App\Services\BiteshipService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TransactionTrackingTest extends TestCase
{
    use DatabaseTransactions;

    private function makeTransaction(array $attributes = []): Transaction
    {
        $customer = Customer::query()->firstOrFail();

        return Transaction::create(array_merge([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-TRACK-' . uniqid(),
            'transaction_date' => now(),
            'payment_method' => 'VA',
            'subtotal' => 100000,
            'discount' => 0,
            'shipping' => 8000,
            'total' => 108000,
            'status' => Transaction::ORDER_SHIPPED,
            'source' => 'Website',
            'shipping_name' => 'Tracking Test',
            'shipping_email' => 'tracking-test@example.com',
            'shipping_phone' => '081234567890',
            'shipping_address' => 'Alamat Test',
            'shipping_district' => 'Banjarmasin Selatan',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
        ], $attributes));
    }

    private function makeShipment(
        Transaction $transaction,
        array $attributes = []
    ): TransactionShipment {
        return TransactionShipment::create(array_merge([
            'transaction_id' => $transaction->id,
            'shipment_type' => TransactionShipment::TYPE_SEPARATE,
            'shipping_method' => 'Kurir',
            'shipping_cost' => 8000,
            'shipping_name' => 'Tracking Test',
            'shipping_email' => 'tracking-test@example.com',
            'shipping_phone' => '081234567890',
            'shipping_address' => 'Alamat Test',
            'shipping_district' => 'Banjarmasin Selatan',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'biteship_order_id' => 'ORDER-TRACK-TEST',
            'biteship_tracking_id' => 'TRACK-TEST',
            'status' => TransactionShipment::STATUS_SHIPPED,
        ], $attributes));
    }

    public function test_shipment_tracking_accepts_empty_status_and_history(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();

        $shipment = $this->makeShipment($transaction, [
            'tracking_number' => null,
            'biteship_waybill_id' => null,
            'biteship_status' => null,
        ]);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getTracking')
                ->once()
                ->with('TRACK-TEST')
                ->andReturn([
                    'success' => true,
                    'tracking_id' => 'TRACK-TEST',
                    'waybill_id' => null,
                    'courier_code' => 'jnt',
                    'status' => null,
                    'history' => [],
                ]);

            $mock->shouldReceive('getOrder')
                ->once()
                ->with('ORDER-TRACK-TEST')
                ->andReturn([
                    'success' => true,
                    'raw' => ['courier' => ['history' => []]],
                ]);
        });

        $this->getJson(route(
            'admin.transactions.shipments.tracking',
            [$transaction, $shipment]
        ))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tracking_number', null)
            ->assertJsonPath('data.status', null)
            ->assertJsonPath('data.history', [])
            ->assertJsonPath('data.proof_images', []);
    }

    public function test_shipment_tracking_uses_waybill_fallback_from_biteship(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();

        $shipment = $this->makeShipment($transaction, [
            'tracking_number' => null,
            'biteship_waybill_id' => null,
        ]);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getTracking')
                ->once()
                ->with('TRACK-TEST')
                ->andReturn([
                    'success' => true,
                    'tracking_id' => 'TRACK-TEST',
                    'waybill_id' => 'WB-FALLBACK-TEST',
                    'courier_code' => 'jnt',
                    'status' => 'in_transit',
                    'history' => [],
                ]);

            $mock->shouldReceive('getOrder')
                ->once()
                ->with('ORDER-TRACK-TEST')
                ->andReturn([
                    'success' => true,
                    'raw' => ['courier' => ['history' => []]],
                ]);
        });

        $this->getJson(route(
            'admin.transactions.shipments.tracking',
            [$transaction, $shipment]
        ))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'data.tracking_number',
                'WB-FALLBACK-TEST'
            )
            ->assertJsonPath('data.status', 'in_transit');
    }

    public function test_shipment_tracking_rejects_shipment_from_another_transaction(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();
        $anotherTransaction = $this->makeTransaction();
        $shipment = $this->makeShipment($anotherTransaction);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldNotReceive('getTracking');
            $mock->shouldNotReceive('getOrder');
        });

        $this->getJson(route(
            'admin.transactions.shipments.tracking',
            [$transaction, $shipment]
        ))->assertNotFound();
    }
    public function test_shipment_label_renders_recipient_and_fulfillment_items(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();
        $shipment = $this->makeShipment($transaction, [
            'shipping_name' => 'Penerima Label Baru',
            'shipping_address' => 'Jalan Label Nomor 123',
            'tracking_number' => 'RESI-LABEL-123',
            'courier' => 'J&T Express',
            'courier_code' => 'jnt',
        ]);

        $fulfillment = \App\Models\TransactionFulfillment::create([
            'transaction_id' => $transaction->id,
            'group_type' => \App\Models\TransactionFulfillment::GROUP_READY,
            'shipping_method' => 'Kurir',
            'shipping_cost' => 8000,
            'status' => \App\Models\TransactionFulfillment::STATUS_SHIPPED,
        ]);

        $product = \App\Models\Product::create([
            'product_code' => 'TEST-LABEL-NEW-' . uniqid(),
            'category_id' => 6,
            'name' => 'Jersey Tes Label Baru',
            'slug' => 'jersey-tes-label-baru-' . uniqid(),
            'description' => 'Produk untuk tes label',
            'material' => 'Mesh',
            'availability' => 'ready',
            'status' => true,
            'customization_enabled' => false,
            'customization_price' => 0,
            'longsleeve_enabled' => false,
            'longsleeve_price' => 0,
            'patch_enabled' => false,
            'patch_price' => 0,
        ]);

        $variant = \App\Models\ProductVariant::create([
            'product_id' => $product->id,
            'size_id' => 1,
            'color_id' => 1,
            'sku' => 'TEST-LABEL-NEW-' . uniqid(),
            'price' => 50000,
            'weight' => 200,
        ]);

        \App\Models\TransactionItem::create([
            'transaction_id' => $transaction->id,
            'transaction_fulfillment_id' => $fulfillment->id,
            'product_variant_id' => $variant->id,
            'custom_name' => null,
            'qty' => 2,
            'price' => 50000,
            'subtotal' => 100000,
            'weight' => 200,
        ]);

        $shipment->fulfillments()->attach($fulfillment->id);

        $this->get(route(
            'admin.transactions.shipments.label',
            [$transaction, $shipment]
        ))
            ->assertOk()
            ->assertSee('Penerima Label Baru')
            ->assertSee('Jalan Label Nomor 123')
            ->assertSee('Jersey Tes Label Baru')
            ->assertSee('RESI-LABEL-123')
            ->assertSee('INV-TRACK-', false);
    }

    public function test_legacy_transaction_label_renders_transaction_items(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();

        $product = \App\Models\Product::create([
            'product_code' => 'TEST-LABEL-LEGACY-' . uniqid(),
            'category_id' => 6,
            'name' => 'Jersey Tes Label Lama',
            'slug' => 'jersey-tes-label-lama-' . uniqid(),
            'description' => 'Produk untuk tes label transaksi lama',
            'material' => 'Mesh',
            'availability' => 'ready',
            'status' => true,
            'customization_enabled' => false,
            'customization_price' => 0,
            'longsleeve_enabled' => false,
            'longsleeve_price' => 0,
            'patch_enabled' => false,
            'patch_price' => 0,
        ]);

        $variant = \App\Models\ProductVariant::create([
            'product_id' => $product->id,
            'size_id' => 1,
            'color_id' => 1,
            'sku' => 'TEST-LABEL-LEGACY-' . uniqid(),
            'price' => 50000,
            'weight' => 250,
        ]);

        \App\Models\TransactionItem::create([
            'transaction_id' => $transaction->id,
            'transaction_fulfillment_id' => null,
            'product_variant_id' => $variant->id,
            'custom_name' => null,
            'qty' => 3,
            'price' => 50000,
            'subtotal' => 150000,
            'weight' => 250,
        ]);

        $this->get(route(
            'admin.transactions.legacy-label',
            $transaction
        ))
            ->assertOk()
            ->assertSee('Tracking Test')
            ->assertSee('Alamat Test')
            ->assertSee('Jersey Tes Label Lama')
            ->assertSee('INV-TRACK-', false);
    }

    public function test_legacy_transaction_label_is_rejected_when_shipment_exists(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();
        $this->makeShipment($transaction);

        $this->get(route(
            'admin.transactions.legacy-label',
            $transaction
        ))->assertNotFound();
    }


    public function test_fulfillment_label_only_contains_its_own_items(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();

        $ready = $this->makeFulfillment($transaction, [
            'group_type' => \App\Models\TransactionFulfillment::GROUP_READY,
            'shipping_name' => 'Penerima Ready',
            'biteship_order_id' => 'ORDER-FULFILLMENT-READY-TEST',
            'biteship_tracking_id' => 'TRACK-FULFILLMENT-READY-TEST',
        ]);

        $preOrder = $this->makeFulfillment($transaction, [
            'group_type' => \App\Models\TransactionFulfillment::GROUP_PRE_ORDER,
            'shipping_name' => 'Penerima Pre Order',
            'biteship_order_id' => 'ORDER-FULFILLMENT-PREORDER-TEST',
            'biteship_tracking_id' => 'TRACK-FULFILLMENT-PREORDER-TEST',
        ]);

        $createProductItem = function (
            \App\Models\TransactionFulfillment $fulfillment,
            string $name
        ) use ($transaction): void {
            $suffix = uniqid();

            $product = \App\Models\Product::create([
                'product_code' => 'TEST-FULFILLMENT-LABEL-' . $suffix,
                'category_id' => 6,
                'name' => $name,
                'slug' => 'test-fulfillment-label-' . strtolower($suffix),
                'description' => 'Produk untuk tes label fulfillment',
                'material' => 'Mesh',
                'availability' => 'ready',
                'status' => true,
                'customization_enabled' => false,
                'customization_price' => 0,
                'longsleeve_enabled' => false,
                'longsleeve_price' => 0,
                'patch_enabled' => false,
                'patch_price' => 0,
            ]);

            $variant = \App\Models\ProductVariant::create([
                'product_id' => $product->id,
                'size_id' => 1,
                'color_id' => 1,
                'sku' => 'TEST-FULFILLMENT-LABEL-' . $suffix,
                'price' => 50000,
                'weight' => 200,
            ]);

            \App\Models\TransactionItem::create([
                'transaction_id' => $transaction->id,
                'transaction_fulfillment_id' => $fulfillment->id,
                'product_variant_id' => $variant->id,
                'custom_name' => null,
                'qty' => 1,
                'price' => 50000,
                'subtotal' => 50000,
                'weight' => 200,
            ]);
        };

        $createProductItem($ready, 'Produk Khusus Ready Stock');
        $createProductItem($preOrder, 'Produk Khusus Pre Order');

        $this->get(route(
            'admin.transactions.fulfillments.label',
            [$transaction, $ready]
        ))
            ->assertOk()
            ->assertSee('Produk Khusus Ready Stock')
            ->assertDontSee('Produk Khusus Pre Order')
            ->assertSee('Penerima Ready');

        $this->get(route(
            'admin.transactions.fulfillments.label',
            [$transaction, $preOrder]
        ))
            ->assertOk()
            ->assertSee('Produk Khusus Pre Order')
            ->assertDontSee('Produk Khusus Ready Stock')
            ->assertSee('Penerima Pre Order');
    }

    public function test_fulfillment_label_rejects_fulfillment_from_another_transaction(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();
        $anotherTransaction = $this->makeTransaction();
        $fulfillment = $this->makeFulfillment($anotherTransaction);

        $this->get(route(
            'admin.transactions.fulfillments.label',
            [$transaction, $fulfillment]
        ))->assertNotFound();
    }
    private function makeFulfillment(
        Transaction $transaction,
        array $attributes = []
    ): \App\Models\TransactionFulfillment {
        return \App\Models\TransactionFulfillment::create(array_merge([
            'transaction_id' => $transaction->id,
            'group_type' => \App\Models\TransactionFulfillment::GROUP_READY,
            'shipping_method' => 'Kurir',
            'shipping_cost' => 8000,
            'shipping_name' => 'Tracking Test',
            'shipping_email' => 'tracking-test@example.com',
            'shipping_phone' => '081234567890',
            'shipping_address' => 'Alamat Test',
            'shipping_district' => 'Banjarmasin Selatan',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'biteship_order_id' => 'ORDER-FULFILLMENT-TEST',
            'biteship_tracking_id' => 'TRACK-FULFILLMENT-TEST',
            'status' => \App\Models\TransactionFulfillment::STATUS_SHIPPED,
        ], $attributes));
    }

    public function test_fulfillment_tracking_returns_tracking_data(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();
        $fulfillment = $this->makeFulfillment($transaction);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getTracking')
                ->once()
                ->with('TRACK-FULFILLMENT-TEST')
                ->andReturn([
                    'success' => true,
                    'tracking_id' => 'TRACK-FULFILLMENT-TEST',
                    'waybill_id' => 'WB-FULFILLMENT-TEST',
                    'courier_code' => 'jnt',
                    'status' => 'in_transit',
                    'history' => [
                        ['status' => 'picked_up', 'note' => 'Paket dijemput'],
                    ],
                ]);

            $mock->shouldReceive('getOrder')
                ->once()
                ->with('ORDER-FULFILLMENT-TEST')
                ->andReturn([
                    'success' => true,
                    'raw' => ['courier' => ['history' => []]],
                ]);
        });

        $this->getJson(route(
            'admin.transactions.fulfillments.tracking',
            [$transaction, $fulfillment]
        ))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tracking_number', 'WB-FULFILLMENT-TEST')
            ->assertJsonPath('data.courier', 'jnt')
            ->assertJsonPath('data.status', 'in_transit')
            ->assertJsonCount(1, 'data.history');
    }

    public function test_fulfillment_tracking_accepts_empty_status_and_history(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();
        $fulfillment = $this->makeFulfillment($transaction, [
            'tracking_number' => null,
            'biteship_waybill_id' => null,
            'courier' => null,
            'courier_code' => null,
            'biteship_status' => null,
        ]);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getTracking')
                ->once()
                ->with('TRACK-FULFILLMENT-TEST')
                ->andReturn([
                    'success' => true,
                    'tracking_id' => 'TRACK-FULFILLMENT-TEST',
                    'waybill_id' => null,
                    'courier_code' => null,
                    'status' => null,
                    'history' => [],
                ]);

            $mock->shouldReceive('getOrder')
                ->once()
                ->with('ORDER-FULFILLMENT-TEST')
                ->andReturn([
                    'success' => true,
                    'raw' => ['courier' => ['history' => []]],
                ]);
        });

        $this->getJson(route(
            'admin.transactions.fulfillments.tracking',
            [$transaction, $fulfillment]
        ))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tracking_number', null)
            ->assertJsonPath('data.courier', null)
            ->assertJsonPath('data.status', null)
            ->assertJsonPath('data.history', []);
    }

    public function test_fulfillment_tracking_rejects_fulfillment_from_another_transaction(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();
        $anotherTransaction = $this->makeTransaction();
        $fulfillment = $this->makeFulfillment($anotherTransaction);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldNotReceive('getTracking');
            $mock->shouldNotReceive('getOrder');
        });

        $this->getJson(route(
            'admin.transactions.fulfillments.tracking',
            [$transaction, $fulfillment]
        ))->assertNotFound();
    }

    public function test_legacy_transaction_tracking_returns_proof_images(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction([
            'biteship_order_id' => 'ORDER-LEGACY-PROOF-TEST',
            'biteship_tracking_id' => 'TRACK-LEGACY-PROOF-TEST',
        ]);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getTracking')
                ->once()
                ->with('TRACK-LEGACY-PROOF-TEST')
                ->andReturn([
                    'success' => true,
                    'waybill_id' => 'WB-LEGACY-PROOF-TEST',
                    'courier_code' => 'jnt',
                    'status' => 'delivered',
                    'history' => [],
                ]);

            $mock->shouldReceive('getOrder')
                ->once()
                ->with('ORDER-LEGACY-PROOF-TEST')
                ->andReturn([
                    'success' => true,
                    'raw' => [
                        'courier' => [
                            'history' => [
                                [
                                    'proof_of_delivery_images' => [
                                        'https://cdn.example.com/legacy-proof.jpg',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]);
        });

        $this->getJson(route(
            'admin.transactions.legacy-tracking',
            $transaction
        ))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.proof_images', [
                'https://cdn.example.com/legacy-proof.jpg',
            ]);
    }

    public function test_shipment_tracking_extracts_proof_images_from_both_formats(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();
        $shipment = $this->makeShipment($transaction);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getTracking')
                ->once()
                ->with('TRACK-TEST')
                ->andReturn([
                    'success' => true,
                    'waybill_id' => 'WB-TRACK-TEST',
                    'courier_code' => 'jnt',
                    'status' => 'delivered',
                    'history' => [],
                ]);

            $mock->shouldReceive('getOrder')
                ->once()
                ->with('ORDER-TRACK-TEST')
                ->andReturn([
                    'success' => true,
                    'raw' => [
                        'courier' => [
                            'history' => [
                                [
                                    'proof_of_delivery_images' => 'https://cdn.example.com/proof-string.jpg',
                                ],
                                [
                                    'images' => [
                                        [
                                            'images_urls' => [
                                                'https://cdn.example.com/proof-array.jpg',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]);
        });

        $this->getJson(route(
            'admin.transactions.shipments.tracking',
            [$transaction, $shipment]
        ))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.proof_images', [
                'https://cdn.example.com/proof-string.jpg',
                'https://cdn.example.com/proof-array.jpg',
            ]);
    }

    public function test_fulfillment_tracking_returns_proof_images(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();
        $fulfillment = $this->makeFulfillment($transaction);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getTracking')
                ->once()
                ->with('TRACK-FULFILLMENT-TEST')
                ->andReturn([
                    'success' => true,
                    'waybill_id' => 'WB-FULFILLMENT-TEST',
                    'courier_code' => 'jnt',
                    'status' => 'delivered',
                    'history' => [],
                ]);

            $mock->shouldReceive('getOrder')
                ->once()
                ->with('ORDER-FULFILLMENT-TEST')
                ->andReturn([
                    'success' => true,
                    'raw' => [
                        'courier' => [
                            'history' => [
                                [
                                    'proof_of_delivery_images' => [
                                        'https://cdn.example.com/fulfillment-proof.jpg',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]);
        });

        $this->getJson(route(
            'admin.transactions.fulfillments.tracking',
            [$transaction, $fulfillment]
        ))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.proof_images', [
                'https://cdn.example.com/fulfillment-proof.jpg',
            ]);
    }

    public function test_proof_images_ignore_insecure_invalid_and_duplicate_urls(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();
        $shipment = $this->makeShipment($transaction);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getTracking')
                ->once()
                ->with('TRACK-TEST')
                ->andReturn([
                    'success' => true,
                    'waybill_id' => 'WB-TRACK-TEST',
                    'courier_code' => 'jnt',
                    'status' => 'delivered',
                    'history' => [],
                ]);

            $mock->shouldReceive('getOrder')
                ->once()
                ->with('ORDER-TRACK-TEST')
                ->andReturn([
                    'success' => true,
                    'raw' => [
                        'courier' => [
                            'history' => [
                                [
                                    'proof_of_delivery_images' => [
                                        'http://cdn.example.com/insecure.jpg',
                                        'not-a-valid-url',
                                        'https://cdn.example.com/valid.jpg',
                                        123,
                                    ],
                                ],
                                [
                                    'images' => [
                                        [
                                            'images_urls' => [
                                                'https://cdn.example.com/valid.jpg',
                                                'https://cdn.example.com/second.jpg',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]);
        });

        $this->getJson(route(
            'admin.transactions.shipments.tracking',
            [$transaction, $shipment]
        ))
            ->assertOk()
            ->assertJsonPath('data.proof_images', [
                'https://cdn.example.com/valid.jpg',
                'https://cdn.example.com/second.jpg',
            ]);
    }

    public function test_tracking_still_succeeds_when_get_order_fails(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();
        $shipment = $this->makeShipment($transaction);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getTracking')
                ->once()
                ->with('TRACK-TEST')
                ->andReturn([
                    'success' => true,
                    'waybill_id' => 'WB-TRACK-TEST',
                    'courier_code' => 'jnt',
                    'status' => 'in_transit',
                    'history' => [
                        ['status' => 'in_transit', 'note' => 'Paket dalam perjalanan'],
                    ],
                ]);

            $mock->shouldReceive('getOrder')
                ->once()
                ->with('ORDER-TRACK-TEST')
                ->andThrow(new \RuntimeException('Biteship order gagal dimuat.'));
        });

        $this->getJson(route(
            'admin.transactions.shipments.tracking',
            [$transaction, $shipment]
        ))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'in_transit')
            ->assertJsonCount(1, 'data.history')
            ->assertJsonPath('data.proof_images', []);
    }

    public function test_shipment_live_tracking_is_returned_for_gojek(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();

        $shipment = $this->makeShipment($transaction, [
            'tracking_number' => 'RESI-GOJEK',
            'courier' => 'Gojek',
            'courier_code' => ' GOJEK ',
        ]);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getTracking')
                ->once()
                ->with('TRACK-TEST')
                ->andReturn([
                    'success' => true,
                    'waybill_id' => 'WB-GOJEK',
                    'courier_code' => 'gojek',
                    'status' => 'in_transit',
                    'history' => [],
                ]);

            $mock->shouldReceive('getOrder')
                ->once()
                ->with('ORDER-TRACK-TEST')
                ->andReturn([
                    'success' => true,
                    'raw' => [
                        'courier' => [
                            'link' => 'https://track.example.com/gojek',
                            'history' => [],
                        ],
                    ],
                ]);
        });

        $this->getJson(route(
            'admin.transactions.shipments.tracking',
            [$transaction, $shipment]
        ))
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
    public function test_shipment_live_tracking_is_hidden_for_non_gojek(
        ?string $storedCourier,
        ?string $expectedCourierCode
    ): void {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();

        $shipment = $this->makeShipment($transaction, [
            'tracking_number' => 'RESI-NON-GOJEK',
            'courier' => null,
            'courier_code' => $storedCourier,
        ]);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getTracking')
                ->once()
                ->with('TRACK-TEST')
                ->andReturn([
                    'success' => true,
                    'waybill_id' => 'WB-NON-GOJEK',
                    'courier_code' => null,
                    'status' => 'in_transit',
                    'history' => [],
                ]);

            $mock->shouldReceive('getOrder')
                ->once()
                ->with('ORDER-TRACK-TEST')
                ->andReturn([
                    'success' => true,
                    'raw' => [
                        'courier' => [
                            'link' => 'https://track.example.com/non-gojek',
                            'history' => [],
                        ],
                    ],
                ]);
        });

        $this->getJson(route(
            'admin.transactions.shipments.tracking',
            [$transaction, $shipment]
        ))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.courier_code', $expectedCourierCode)
            ->assertJsonPath('data.live_tracking_allowed', false)
            ->assertJsonPath('data.live_tracking_url', null);
    }

    public function test_shipment_live_tracking_is_hidden_for_gojek_without_https_url(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();

        $shipment = $this->makeShipment($transaction, [
            'tracking_number' => 'RESI-GOJEK-NO-LINK',
            'courier' => 'Gojek',
            'courier_code' => 'gojek',
        ]);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getTracking')
                ->once()
                ->with('TRACK-TEST')
                ->andReturn([
                    'success' => true,
                    'waybill_id' => 'WB-GOJEK-NO-LINK',
                    'courier_code' => 'gojek',
                    'status' => 'in_transit',
                    'history' => [],
                ]);

            $mock->shouldReceive('getOrder')
                ->once()
                ->with('ORDER-TRACK-TEST')
                ->andReturn([
                    'success' => true,
                    'raw' => [
                        'courier' => [
                            'link' => 'http://track.example.com/insecure',
                            'history' => [],
                        ],
                    ],
                ]);
        });

        $this->getJson(route(
            'admin.transactions.shipments.tracking',
            [$transaction, $shipment]
        ))
            ->assertOk()
            ->assertJsonPath('data.live_tracking_allowed', true)
            ->assertJsonPath('data.live_tracking_url', null);
    }

    public function test_each_shipment_live_tracking_follows_its_own_courier(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));

        $transaction = $this->makeTransaction();

        $gojekShipment = $this->makeShipment($transaction, [
            'shipment_type' => TransactionShipment::TYPE_SEPARATE,
            'courier' => 'Gojek',
            'courier_code' => 'gojek',
            'biteship_order_id' => 'ORDER-GOJEK',
            'biteship_tracking_id' => 'TRACK-GOJEK',
        ]);

        $jntShipment = $this->makeShipment($transaction, [
            'shipment_type' => TransactionShipment::TYPE_SEPARATE,
            'courier' => 'J&T Express',
            'courier_code' => 'jnt',
            'biteship_order_id' => 'ORDER-JNT',
            'biteship_tracking_id' => 'TRACK-JNT',
        ]);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getTracking')
                ->once()
                ->with('TRACK-GOJEK')
                ->andReturn([
                    'success' => true,
                    'waybill_id' => 'WB-GOJEK',
                    'courier_code' => 'gojek',
                    'status' => 'in_transit',
                    'history' => [],
                ]);

            $mock->shouldReceive('getTracking')
                ->once()
                ->with('TRACK-JNT')
                ->andReturn([
                    'success' => true,
                    'waybill_id' => 'WB-JNT',
                    'courier_code' => 'jnt',
                    'status' => 'in_transit',
                    'history' => [],
                ]);

            $mock->shouldReceive('getOrder')
                ->once()
                ->with('ORDER-GOJEK')
                ->andReturn([
                    'success' => true,
                    'raw' => [
                        'courier' => [
                            'link' => 'https://track.example.com/gojek',
                            'history' => [],
                        ],
                    ],
                ]);

            $mock->shouldReceive('getOrder')
                ->once()
                ->with('ORDER-JNT')
                ->andReturn([
                    'success' => true,
                    'raw' => [
                        'courier' => [
                            'link' => 'https://track.example.com/jnt',
                            'history' => [],
                        ],
                    ],
                ]);
        });

        $this->getJson(route(
            'admin.transactions.shipments.tracking',
            [$transaction, $gojekShipment]
        ))
            ->assertOk()
            ->assertJsonPath('data.live_tracking_allowed', true)
            ->assertJsonPath(
                'data.live_tracking_url',
                'https://track.example.com/gojek'
            );

        $this->getJson(route(
            'admin.transactions.shipments.tracking',
            [$transaction, $jntShipment]
        ))
            ->assertOk()
            ->assertJsonPath('data.live_tracking_allowed', false)
            ->assertJsonPath('data.live_tracking_url', null);
    }
}
