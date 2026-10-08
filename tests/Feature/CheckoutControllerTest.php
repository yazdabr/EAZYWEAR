<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Services\DokuService;
use App\Services\BiteshipService;
use App\Models\Transaction;
use App\Services\DokuQrisService;
use App\Models\FulfillmentSlot;
use App\Models\FulfillmentHold;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function mockDokuSuccess(): void
    {
        $this->mock(DokuService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')
                ->andReturn(true);

            $mock->shouldReceive('createVirtualAccount')
                ->once()
                ->andReturn([
                    'responseCode' => '2002500',
                    'responseMessage' => 'Success',
                    'virtualAccountNo' => '190089123456789012',
                    'paymentRequestId' => 'PAY-TEST-001',
                    '_external_id' => 'EXT-TEST-001',
                ]);
        });
    }

    private function mockQrisSuccess(): void
    {
        $this->mock(DokuQrisService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')
                ->andReturn(true);

            $mock->shouldReceive('generateQr')
                ->once()
                ->andReturnUsing(function (
                    string $partnerReferenceNo,
                    $amount,
                    ?string $validityPeriod = null
                ) {
                    return [
                        'responseCode' => '2004700',
                        'responseMessage' => 'Successful',
                        'referenceNo' => 'DOKU-QRIS-REF-001',
                        'partnerReferenceNo' => $partnerReferenceNo,
                        'qrContent' => '000201010212...',
                        'terminalId' => 'TEST-TERMINAL-ID',
                        '_external_id' => 'EXT-QRIS-001',
                    ];
                });
        });
    }

    public function test_checkout_pickup_does_not_call_biteship_and_saves_pickup_details(): void
    {
        $variant = ProductVariant::query()
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->firstOrFail();

        $variant->update([
            'weight' => 250,
        ]);

        Inventory::query()
            ->where('product_variant_id', $variant->id)
            ->update([
                'stock' => 10,
            ]);

        $databasePrice = (float) $variant->price;

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => $databasePrice,
                'qty' => 1,
                'custom_name' => '',
                'custom_number' => '',
            ],
        ]);

        $biteship = $this->mock(BiteshipService::class);

        $biteship->shouldReceive('getCourierRates')
            ->never();

        $this->mockDokuSuccess();

        $pickupDate = now()->addDay()->format('Y-m-d');

        $response = $this->post(route('checkout.store'), [
            'name' => 'Pickup Customer',
            'email' => 'pickup-test@test.com',
            'phone' => '08123456780',
            'shipping_address' => 'Alamat Pickup',
            'shipping_district' => 'District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',

            'shipping_method' => 'Ambil di Tempat',

            'pickup_date' => $pickupDate,
            'pickup_time_start' => '09:00',
            'pickup_time_end' => '11:00',

            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',

            'payment_method' => 'VA',
            'va_bank' => 'MANDIRI',
        ]);

        $response->assertRedirect(route('checkout.success'));

        $transaction = Transaction::query()
            ->where('shipping_email', 'pickup-test@test.com')
            ->firstOrFail();

        $this->assertSame(
            $databasePrice,
            (float) $transaction->subtotal
        );

        $this->assertSame(
            0.0,
            (float) $transaction->shipping
        );

        $this->assertSame(
            $databasePrice - (float) $transaction->discount,
            (float) $transaction->total
        );

        $this->assertSame(
            'Ambil di Tempat',
            $transaction->shipping_method
        );

        $this->assertSame(
            $pickupDate,
            $transaction->pickup_date->toDateString()
        );

        $this->assertSame(
            '09:00:00',
            $transaction->pickup_time_start
        );

        $this->assertSame(
            '11:00:00',
            $transaction->pickup_time_end
        );

        $this->assertNull($transaction->courier_code);
        $this->assertNull($transaction->courier_service_code);

        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $transaction->id,
            'price' => $databasePrice,
        ]);
        $this->assertSame(
            $pickupDate,
            $transaction->fulfillment_date->toDateString()
        );
        $this->assertSame(
            $pickupDate,
            $transaction->pickup_date->toDateString()
        );
    }

    public function test_checkout_creates_pending_transaction_with_va(): void
    {

    $variant = ProductVariant::query()
        ->whereHas('product', function ($query) {
            $query->where('status', true);
        })
        ->firstOrFail();

    $variant->update([
        'weight' => 250,
    ]);

        Inventory::query()
            ->where('product_variant_id', $variant->id)
            ->update([
                'stock' => 10,
            ]);

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => $variant->price,
                'qty' => 1,
                'custom_name' => '',
                'custom_number' => '',
            ],
        ]);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getCourierRates')
                ->once()
                ->andReturn([
                    'success' => true,
                    'rates' => [
                        [
                            'courier_code' => 'jnt',
                            'courier_name' => 'J&T',
                            'service_code' => 'ez',
                            'service_name' => 'EZ',
                            'price' => 8000,
                            'duration' => '2-3 days',
                            'service_type' => 'standard',
                            'shipping_type' => 'parcel',
                        ],
                    ],
                    'raw' => [],
                ]);
        });

        $this->mockDokuSuccess();

        $response = $this->post(route('checkout.store'), [
            'name' => 'Customer Test',
            'email' => 'customer@test.com',
            'phone' => '08123456789',
            'shipping_address' => 'Alamat Test',
            'shipping_district' => 'District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
            'payment_method' => 'VA',
            'va_bank' => 'MANDIRI',
        ]);

        $response
            ->assertRedirect(route('checkout.success'));

        $this->assertDatabaseHas('transactions', [
            'status' => 'PENDING',
            'va_number' => '190089123456789012',
            'payment_method' => 'VA',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
            'shipping' => 8000,
        ]);

        $transaction = \App\Models\Transaction::query()
            ->where('va_number', '190089123456789012')
            ->firstOrFail();

        $this->assertSame(
            (float) $transaction->subtotal + 8000 - (float) $transaction->discount,
            (float) $transaction->total
        );
    }

    public function test_checkout_uses_database_variant_price_not_session_cart_price(): void
    {
        $variant = ProductVariant::query()
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->firstOrFail();

        $variant->update([
            'weight' => 250,
        ]);

        Inventory::query()
            ->where('product_variant_id', $variant->id)
            ->update([
                'stock' => 10,
            ]);

        $databasePrice = (float) $variant->price;

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => 1,
                'qty' => 1,
                'custom_name' => '',
                'custom_number' => '',
            ],
        ]);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getCourierRates')
                ->once()
                ->andReturn([
                    'success' => true,
                    'rates' => [
                        [
                            'courier_code' => 'jnt',
                            'courier_name' => 'J&T',
                            'service_code' => 'ez',
                            'service_name' => 'EZ',
                            'price' => 8000,
                            'duration' => '2-3 days',
                            'service_type' => 'standard',
                            'shipping_type' => 'parcel',
                        ],
                    ],
                    'raw' => [],
                ]);
        });

        $this->mockDokuSuccess();

        $response = $this->post(route('checkout.store'), [
            'name' => 'Customer Test',
            'email' => 'price-test@test.com',
            'phone' => '08123456780',
            'shipping_address' => 'Alamat Test',
            'shipping_district' => 'District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
            'payment_method' => 'VA',
            'va_bank' => 'MANDIRI',
        ]);

        $response->assertRedirect(route('checkout.success'));

        $transaction = \App\Models\Transaction::query()
            ->where('shipping_email', 'price-test@test.com')
            ->firstOrFail();

        $this->assertSame($databasePrice, (float) $transaction->subtotal);

        $this->assertSame(
            $databasePrice + 8000 - (float) $transaction->discount,
            (float) $transaction->total
        );

        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $transaction->id,
            'price' => $databasePrice,
        ]);
    }

    public function test_checkout_keeps_quantity_and_subtotal_consistent(): void
    {
        $variant = ProductVariant::query()
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->firstOrFail();

        $variant->update([
            'weight' => 250,
        ]);

        Inventory::query()
            ->where('product_variant_id', $variant->id)
            ->update([
                'stock' => 10,
            ]);

        $databasePrice = (float) $variant->price;
        $quantity = 3;
        $expectedSubtotal = $databasePrice * $quantity;

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => $variant->price,
                'qty' => $quantity,
                'custom_name' => '',
                'custom_number' => '',
            ],
        ]);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getCourierRates')
                ->once()
                ->andReturn([
                    'success' => true,
                    'rates' => [[
                        'courier_code' => 'jnt',
                        'courier_name' => 'J&T',
                        'service_code' => 'ez',
                        'service_name' => 'EZ',
                        'price' => 8000,
                        'duration' => '2-3 days',
                        'service_type' => 'standard',
                        'shipping_type' => 'parcel',
                    ]],
                    'raw' => [],
                ]);
        });

        $this->mockDokuSuccess();

        $response = $this->post(route('checkout.store'), [
            'name' => 'Customer Test',
            'email' => 'quantity-test@test.com',
            'phone' => '08123456782',
            'shipping_address' => 'Alamat Test',
            'shipping_district' => 'District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
            'payment_method' => 'VA',
            'va_bank' => 'MANDIRI',
        ]);

        $response->assertRedirect(route('checkout.success'));

        $transaction = \App\Models\Transaction::query()
            ->where('shipping_email', 'quantity-test@test.com')
            ->firstOrFail();

        $transactionItem = $transaction->items()->firstOrFail();

        $this->assertSame($quantity, (int) $transactionItem->qty);

        $this->assertSame(
            $expectedSubtotal,
            (float) $transactionItem->subtotal
        );

        $this->assertSame(
            $expectedSubtotal,
            (float) $transaction->subtotal
        );

        $this->assertSame(
            $expectedSubtotal + 8000 - (float) $transaction->discount,
            (float) $transaction->total
        );
    }

    public function test_checkout_keeps_multiple_items_consistent(): void
    {
        $variants = ProductVariant::query()
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->orderBy('id')
            ->take(2)
            ->get();

        $this->assertCount(2, $variants);

        $quantityA = 2;
        $quantityB = 3;

        foreach ($variants as $variant) {
            $variant->update([
                'weight' => 250,
            ]);

            Inventory::query()
                ->where('product_variant_id', $variant->id)
                ->update([
                    'stock' => 10,
                ]);
        }

        $priceA = (float) $variants[0]->price;
        $priceB = (float) $variants[1]->price;

        $subtotalA = $priceA * $quantityA;
        $subtotalB = $priceB * $quantityB;
        $expectedSubtotal = $subtotalA + $subtotalB;

        Session::put('cart', [
            [
                'variant_id' => $variants[0]->id,
                'price' => $variants[0]->price,
                'qty' => $quantityA,
                'custom_name' => '',
                'custom_number' => '',
            ],
            [
                'variant_id' => $variants[1]->id,
                'price' => $variants[1]->price,
                'qty' => $quantityB,
                'custom_name' => '',
                'custom_number' => '',
            ],
        ]);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getCourierRates')
                ->once()
                ->andReturn([
                    'success' => true,
                    'rates' => [[
                        'courier_code' => 'jnt',
                        'courier_name' => 'J&T',
                        'service_code' => 'ez',
                        'service_name' => 'EZ',
                        'price' => 8000,
                        'duration' => '2-3 days',
                        'service_type' => 'standard',
                        'shipping_type' => 'parcel',
                    ]],
                    'raw' => [],
                ]);
        });

        $this->mockDokuSuccess();

        $response = $this->post(route('checkout.store'), [
            'name' => 'Customer Test',
            'email' => 'multi-item@test.com',
            'phone' => '08123456783',
            'shipping_address' => 'Alamat Test',
            'shipping_district' => 'District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
            'payment_method' => 'VA',
            'va_bank' => 'MANDIRI',
        ]);

        $response->assertRedirect(route('checkout.success'));

        $transaction = Transaction::query()
            ->where('shipping_email', 'multi-item@test.com')
            ->firstOrFail();

        $transactionItems = $transaction->items()
            ->orderBy('product_variant_id')
            ->get();

        $this->assertCount(2, $transactionItems);

        $itemA = $transactionItems->firstWhere(
            'product_variant_id',
            $variants[0]->id
        );

        $itemB = $transactionItems->firstWhere(
            'product_variant_id',
            $variants[1]->id
        );

        $this->assertNotNull($itemA);
        $this->assertNotNull($itemB);

        $this->assertSame($quantityA, (int) $itemA->qty);
        $this->assertSame($priceA, (float) $itemA->price);
        $this->assertSame($subtotalA, (float) $itemA->subtotal);

        $this->assertSame($quantityB, (int) $itemB->qty);
        $this->assertSame($priceB, (float) $itemB->price);
        $this->assertSame($subtotalB, (float) $itemB->subtotal);

        $this->assertSame(
            $expectedSubtotal,
            (float) $transaction->subtotal
        );

        $this->assertSame(
            $expectedSubtotal + 8000 - (float) $transaction->discount,
            (float) $transaction->total
        );
    }

    public function test_checkout_fails_when_cart_quantity_exceeds_stock(): void
    {
        $variant = ProductVariant::query()
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->firstOrFail();

        $variant->update([
            'weight' => 250,
        ]);

        Inventory::query()
            ->where('product_variant_id', $variant->id)
            ->update([
                'stock' => 10,
            ]);

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => $variant->price,
                'qty' => 11,
                'custom_name' => '',
                'custom_number' => '',
            ],
        ]);

        $response = $this->post(route('checkout.store'), [
            'name' => 'Customer Test',
            'email' => 'stock-test@test.com',
            'phone' => '08123456781',
            'shipping_address' => 'Alamat Test',
            'shipping_district' => 'District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
            'payment_method' => 'VA',
            'va_bank' => 'MANDIRI',
        ]);

        $response->assertSessionHasErrors('cart');

        $this->assertDatabaseMissing('transactions', [
            'shipping_email' => 'stock-test@test.com',
        ]);
    }

    public function test_checkout_fails_when_doku_create_va_failed(): void
    {
        $variant = ProductVariant::query()
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->firstOrFail();

        $variant->update([
            'weight' => 250,
        ]);

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => $variant->price,
                'qty' => 1,
                'custom_name' => '',
                'custom_number' => '',
            ],
        ]);

        $this->mock(DokuService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')
                ->andReturn(true);

            $mock->shouldReceive('createVirtualAccount')
                ->andReturn([
                    'responseCode' => '4000000',
                    'responseMessage' => 'Failed',
                ]);
        });

        $response = $this->post(route('checkout.store'), [
            'name' => 'Customer Test',
            'email' => 'customer@test.com',
            'phone' => '08123456789',
            'shipping_address' => 'Alamat Test',
            'shipping_district' => 'District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
            'payment_method' => 'VA',
            'va_bank' => 'MANDIRI',
        ]);

        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('transactions', [
            'payment_method' => 'VA',
            'shipping_email' => 'customer@test.com',
        ]);
    }

    public function test_checkout_accepts_all_supported_va_banks(): void
    {
        $supportedBanks = ['MANDIRI', 'BNI', 'BRI', 'BSI'];

        foreach ($supportedBanks as $bank) {
            $variant = ProductVariant::query()
                ->whereHas('product', function ($query) {
                    $query->where('status', true);
                })
                ->firstOrFail();

            $variant->update([
                'weight' => 250,
            ]);

            Inventory::query()
                ->where('product_variant_id', $variant->id)
                ->update([
                    'stock' => 10,
                ]);

            Session::put('cart', [
                [
                    'variant_id' => $variant->id,
                    'price' => $variant->price,
                    'qty' => 1,
                    'custom_name' => '',
                    'custom_number' => '',
                ],
            ]);

            $this->mock(BiteshipService::class, function ($mock) {
                $mock->shouldReceive('getCourierRates')
                    ->once()
                    ->andReturn([
                        'success' => true,
                        'rates' => [
                            [
                                'courier_code' => 'jnt',
                                'courier_name' => 'J&T',
                                'service_code' => 'ez',
                                'service_name' => 'EZ',
                                'price' => 8000,
                                'duration' => '2-3 days',
                                'service_type' => 'standard',
                                'shipping_type' => 'parcel',
                            ],
                        ],
                        'raw' => [],
                    ]);
            });

            $this->mockDokuSuccess();

            $response = $this->post(route('checkout.store'), [
                'name' => 'Bank Test',
                'email' => "bank-test-{$bank}@test.com",
                'phone' => '08123456789',
                'shipping_address' => 'Alamat Test',
                'shipping_district' => 'District',
                'shipping_city' => 'Banjarmasin',
                'shipping_province' => 'Kalimantan Selatan',
                'shipping_postal_code' => '70111',
                'shipping_method' => 'Kurir',
                'courier_code' => 'jnt',
                'courier_service_code' => 'ez',
                'payment_method' => 'VA',
                'va_bank' => $bank,
            ]);

            $this->assertSame(
                302,
                $response->status(),
                "Bank {$bank} seharusnya diterima oleh checkout."
            );

            $this->assertDatabaseHas('transactions', [
                'payment_method' => 'VA',
                'va_bank' => $bank,
                'status' => 'PENDING',
            ]);
        }
    }

    public function test_checkout_rejects_bca_and_empty_va_bank(): void
    {
        foreach (['BCA', null] as $bank) {
            $variant = ProductVariant::query()
                ->whereHas('product', function ($query) {
                    $query->where('status', true);
                })
                ->firstOrFail();

            $variant->update([
                'weight' => 250,
            ]);

            Inventory::query()
                ->where('product_variant_id', $variant->id)
                ->update([
                    'stock' => 10,
                ]);

            Session::put('cart', [
                [
                    'variant_id' => $variant->id,
                    'price' => (float) $variant->price,
                    'qty' => 1,
                    'custom_name' => '',
                    'custom_number' => '',
                ],
            ]);

            $response = $this->post(route('checkout.store'), [
                'name' => 'Bank Test',
                'email' => 'bank-test@test.com',
                'phone' => '08123456789',
                'shipping_address' => 'Alamat Test',
                'shipping_district' => 'District',
                'shipping_city' => 'Banjarmasin',
                'shipping_province' => 'Kalimantan Selatan',
                'shipping_postal_code' => '70111',
                'shipping_method' => 'Kurir',
                'courier_code' => 'jnt',
                'courier_service_code' => 'ez',
                'payment_method' => 'VA',
                'va_bank' => $bank,
            ]);

            $response->assertSessionHasErrors('va_bank');

            $this->assertDatabaseMissing('transactions', [
                'payment_method' => 'VA',
                'shipping_email' => 'bank-test@test.com',
            ]);
        }
    }

    public function test_checkout_creates_pending_transaction_with_qris(): void
    {
        $variant = ProductVariant::query()
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->firstOrFail();

        $variant->update([
            'weight' => 250,
        ]);

        Inventory::query()
            ->where('product_variant_id', $variant->id)
            ->update([
                'stock' => 10,
            ]);

        $databasePrice = (float) $variant->price;

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => $databasePrice,
                'qty' => 1,
                'custom_name' => '',
                'custom_number' => '',
            ],
        ]);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getCourierRates')
                ->once()
                ->andReturn([
                    'success' => true,
                    'rates' => [
                        [
                            'courier_code' => 'jnt',
                            'courier_name' => 'J&T',
                            'service_code' => 'ez',
                            'service_name' => 'EZ',
                            'price' => 8000,
                            'duration' => '2-3 days',
                            'service_type' => 'standard',
                            'shipping_type' => 'parcel',
                        ],
                    ],
                    'raw' => [],
                ]);
        });

        $this->mockQrisSuccess();

        $response = $this->post(route('checkout.store'), [
            'name' => 'QRIS Customer',
            'email' => 'qris-test@test.com',
            'phone' => '08123456789',
            'shipping_address' => 'Alamat QRIS',
            'shipping_district' => 'District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',

            'shipping_method' => 'Kurir',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',

            'payment_method' => 'QRIS',
        ]);

        $response->assertRedirect(route('checkout.success'));

        $transaction = Transaction::query()
            ->where('shipping_email', 'qris-test@test.com')
            ->firstOrFail();

        $this->assertSame('QRIS', $transaction->payment_method);
        $this->assertSame('PENDING', $transaction->status);

        $this->assertSame(
            'DOKU-QRIS-REF-001',
            $transaction->qris_reference_no
        );

        $this->assertSame(
            '000201010212...',
            $transaction->qris_content
        );

        $this->assertNotNull($transaction->qris_expired_at);
        $this->assertNotNull($transaction->qris_response);

        $this->assertNull($transaction->va_number);
        $this->assertNull($transaction->va_bank);

        $this->assertSame(
            $databasePrice + 8000 - (float) $transaction->discount,
            (float) $transaction->total
        );
    }

    public function test_checkout_fails_when_qris_generate_failed(): void
    {
        $variant = ProductVariant::query()
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->firstOrFail();

        $variant->update([
            'weight' => 250,
        ]);

        Inventory::query()
            ->where('product_variant_id', $variant->id)
            ->update([
                'stock' => 10,
            ]);

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => $variant->price,
                'qty' => 1,
                'custom_name' => '',
                'custom_number' => '',
            ],
        ]);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getCourierRates')
                ->once()
                ->andReturn([
                    'success' => true,
                    'rates' => [
                        [
                            'courier_code' => 'jnt',
                            'courier_name' => 'J&T',
                            'service_code' => 'ez',
                            'service_name' => 'EZ',
                            'price' => 8000,
                            'duration' => '2-3 days',
                            'service_type' => 'standard',
                            'shipping_type' => 'parcel',
                        ],
                    ],
                    'raw' => [],
                ]);
        });

        $this->mock(DokuQrisService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')
                ->andReturn(true);

            $mock->shouldReceive('generateQr')
                ->once()
                ->andReturn([
                    'responseCode' => '4000000',
                    'responseMessage' => 'Failed',
                ]);
        });

        $response = $this->post(route('checkout.store'), [
            'name' => 'QRIS Failed Customer',
            'email' => 'qris-failed@test.com',
            'phone' => '08123456789',
            'shipping_address' => 'Alamat Test',
            'shipping_district' => 'District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',

            'shipping_method' => 'Kurir',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',

            'payment_method' => 'QRIS',
        ]);

        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('transactions', [
            'payment_method' => 'QRIS',
            'shipping_email' => 'qris-failed@test.com',
        ]);
    }

    public function test_checkout_qris_does_not_call_doku_virtual_account(): void
    {
        $variant = ProductVariant::query()
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->firstOrFail();

        $variant->update([
            'weight' => 250,
        ]);

        Inventory::query()
            ->where('product_variant_id', $variant->id)
            ->update([
            'stock' => 10,
        ]);

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => $variant->price,
                'qty' => 1,
                'custom_name' => '',
                'custom_number' => '',
            ],
        ]);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getCourierRates')
                ->once()
                ->andReturn([
                    'success' => true,
                    'rates' => [
                        [
                            'courier_code' => 'jnt',
                            'courier_name' => 'J&T',
                            'service_code' => 'ez',
                            'service_name' => 'EZ',
                            'price' => 8000,
                            'duration' => '2-3 days',
                            'service_type' => 'standard',
                            'shipping_type' => 'parcel',
                        ],
                    ],
                    'raw' => [],
                ]);
        });

        $doku = $this->mock(DokuService::class);

        $doku->shouldReceive('createVirtualAccount')
            ->never();

        $this->mockQrisSuccess();

        $response = $this->post(route('checkout.store'), [
            'name' => 'QRIS Customer',
            'email' => 'qris-no-va@test.com',
            'phone' => '08123456789',
            'shipping_address' => 'Alamat Test',
            'shipping_district' => 'District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',

            'shipping_method' => 'Kurir',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',

            'payment_method' => 'QRIS',
        ]);

        $response->assertRedirect(route('checkout.success'));
    }

    private function customizationVariant(
        bool $enabled,
        int $price
    ): ProductVariant {
        $variant = ProductVariant::query()
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->firstOrFail();

        $variant->product->update([
            'customization_enabled' => $enabled,
            'customization_price' => $price,
        ]);

        $variant->update([
            'weight' => 250,
        ]);

        Inventory::query()->updateOrCreate(
            [
                'product_variant_id' => $variant->id,
            ],
            [
                'stock' => 10,
            ]
        );

        return $variant->fresh(['product']);
    }

    private function customizationCheckout(
        ProductVariant $variant,
        string $email,
        string $customName = '',
        string $customNumber = ''
    ) {
        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => 1,
                'qty' => 1,
                'custom_name' => $customName,
                'custom_number' => $customNumber,
            ],
        ]);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getCourierRates')
                ->once()
                ->andReturn([
                    'success' => true,
                    'rates' => [[
                        'courier_code' => 'jnt',
                        'courier_name' => 'J&T',
                        'service_code' => 'ez',
                        'service_name' => 'EZ',
                        'price' => 8000,
                        'duration' => '2-3 days',
                        'service_type' => 'standard',
                        'shipping_type' => 'parcel',
                    ]],
                    'raw' => [],
                ]);
        });

        $this->mockDokuSuccess();

        return $this->post(route('checkout.store'), [
            'name' => 'Customization Test',
            'email' => $email,
            'phone' => '08123456789',
            'shipping_address' => 'Alamat Test',
            'shipping_district' => 'District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
            'payment_method' => 'VA',
            'va_bank' => 'MANDIRI',
        ]);
    }

    public function test_checkout_custom_off_without_input_has_no_fee(): void
    {
        $variant = $this->customizationVariant(false, 125000);

        $response = $this->customizationCheckout(
            $variant,
            'custom-off-empty@test.com'
        );

        $response->assertRedirect(route('checkout.success'));

        $transaction = Transaction::query()
            ->latest('id')
            ->firstOrFail();

        $item = $transaction->items()->firstOrFail();

        $this->assertSame(
            (float) $variant->price,
            (float) $item->price
        );

        $this->assertSame(
            (float) $variant->price,
            (float) $transaction->subtotal
        );
    }

    public function test_checkout_custom_on_without_input_has_no_fee(): void
    {
        $variant = $this->customizationVariant(true, 125000);

        $response = $this->customizationCheckout(
            $variant,
            'custom-on-empty@test.com'
        );

        $response->assertRedirect(route('checkout.success'));

        $transaction = Transaction::query()
            ->latest('id')
            ->firstOrFail();

        $item = $transaction->items()->firstOrFail();

        $this->assertSame(
            (float) $variant->price,
            (float) $item->price
        );

        $this->assertSame(
            (float) $variant->price,
            (float) $transaction->subtotal
        );
    }

    public function test_checkout_custom_on_with_name_uses_database_fee_once(): void
    {
        $variant = $this->customizationVariant(true, 125000);

        $response = $this->customizationCheckout(
            $variant,
            'custom-on-name@test.com',
            'MESSI'
        );

        $response->assertRedirect(route('checkout.success'));

        $transaction = Transaction::query()
            ->latest('id')
            ->firstOrFail();

        $item = $transaction->items()->firstOrFail();

        $expectedPrice = (float) $variant->price + 125000;

        $this->assertSame(
            $expectedPrice,
            (float) $item->price
        );

        $this->assertSame(
            $expectedPrice,
            (float) $transaction->subtotal
        );
    }

    public function test_checkout_custom_on_with_name_and_number_uses_database_fee_only_once(): void
    {
        $variant = $this->customizationVariant(true, 125000);

        $response = $this->customizationCheckout(
            $variant,
            'custom-on-both@test.com',
            'MESSI',
            '10'
        );

        $response->assertRedirect(route('checkout.success'));

        $transaction = Transaction::query()
            ->latest('id')
            ->firstOrFail();

        $item = $transaction->items()->firstOrFail();

        $expectedPrice = (float) $variant->price + 125000;

        $this->assertSame(
            $expectedPrice,
            (float) $item->price
        );

        $this->assertSame(
            $expectedPrice,
            (float) $transaction->subtotal
        );
    }

    public function test_checkout_custom_off_with_input_is_rejected(): void
    {
        $variant = $this->customizationVariant(false, 125000);

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => 1,
                'qty' => 1,
                'custom_name' => 'MESSI',
                'custom_number' => '10',
            ],
        ]);

        $response = $this->post(route('checkout.store'), [
            'name' => 'Customization Test',
            'email' => 'custom-off-input@test.com',
            'phone' => '08123456789',
            'shipping_address' => 'Alamat Test',
            'shipping_district' => 'District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
            'payment_method' => 'VA',
            'va_bank' => 'MANDIRI',
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHasErrors(['cart' => 'Produk ini tidak menyediakan custom nama atau nomor.']);

    }


    public function test_shipping_rates_custom_off_without_input_uses_database_variant_price(): void
    {
        $variant = $this->customizationVariant(false, 125000);

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => 1,
                'qty' => 1,
                'custom_name' => '',
                'custom_number' => '',
            ],
        ]);

        $this->mock(BiteshipService::class, function ($mock) use ($variant) {
            $mock->shouldReceive('getCourierRates')
                ->once()
                ->withArgs(function (array $payload) use ($variant) {
                    return $payload['items'][0]['value'] === (int) round((float) $variant->price);
                })
                ->andReturn([
                    'success' => true,
                    'rates' => [],
                ]);
        });

        $response = $this->postJson(route('checkout.shipping-rates'), [
            'shipping_postal_code' => '70111',
        ]);

        $response
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_shipping_rates_custom_on_without_input_has_no_fee(): void
    {
        $variant = $this->customizationVariant(true, 125000);

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => 1,
                'qty' => 1,
                'custom_name' => '',
                'custom_number' => '',
            ],
        ]);

        $this->mock(BiteshipService::class, function ($mock) use ($variant) {
            $mock->shouldReceive('getCourierRates')
                ->once()
                ->withArgs(function (array $payload) use ($variant) {
                    return $payload['items'][0]['value'] === (int) round((float) $variant->price);
                })
                ->andReturn([
                    'success' => true,
                    'rates' => [],
                ]);
        });

        $response = $this->postJson(route('checkout.shipping-rates'), [
            'shipping_postal_code' => '70111',
        ]);

        $response
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_shipping_rates_custom_on_with_name_uses_database_fee_once(): void
    {
        $variant = $this->customizationVariant(true, 125000);

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => 1,
                'qty' => 1,
                'custom_name' => 'MESSI',
                'custom_number' => '',
            ],
        ]);

        $this->mock(BiteshipService::class, function ($mock) use ($variant) {
            $mock->shouldReceive('getCourierRates')
                ->once()
                ->withArgs(function (array $payload) use ($variant) {
                    $expectedValue = (int) round((float) $variant->price + 125000);

                    return $payload['items'][0]['value'] === $expectedValue;
                })
                ->andReturn([
                    'success' => true,
                    'rates' => [],
                ]);
        });

        $response = $this->postJson(route('checkout.shipping-rates'), [
            'shipping_postal_code' => '70111',
        ]);

        $response
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_shipping_rates_custom_on_with_name_and_number_uses_database_fee_only_once(): void
    {
        $variant = $this->customizationVariant(true, 125000);

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => 1,
                'qty' => 1,
                'custom_name' => 'MESSI',
                'custom_number' => '10',
            ],
        ]);

        $this->mock(BiteshipService::class, function ($mock) use ($variant) {
            $mock->shouldReceive('getCourierRates')
                ->once()
                ->withArgs(function (array $payload) use ($variant) {
                    $expectedValue = (int) round((float) $variant->price + 125000);

                    return $payload['items'][0]['value'] === $expectedValue;
                })
                ->andReturn([
                    'success' => true,
                    'rates' => [],
                ]);
        });

        $response = $this->postJson(route('checkout.shipping-rates'), [
            'shipping_postal_code' => '70111',
        ]);

        $response
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_shipping_rates_custom_off_with_input_is_rejected(): void
    {
        $variant = $this->customizationVariant(false, 125000);

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => 1,
                'qty' => 1,
                'custom_name' => 'MESSI',
                'custom_number' => '10',
            ],
        ]);

        $biteship = $this->mock(BiteshipService::class);
        $biteship->shouldNotReceive('getCourierRates');

        $response = $this->postJson(route('checkout.shipping-rates'), [
            'shipping_postal_code' => '70111',
        ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Produk ini tidak menyediakan custom nama atau nomor.',
            ]);
    }
    public function test_checkout_rejects_empty_cart(): void
    {
        Session::put('cart', []);

        $response = $this->post(route('checkout.store'), [
            'name' => 'Customer Test',
            'email' => 'customer@test.com',
            'phone' => '08123456789',
            'shipping_address' => 'Alamat Test',
            'shipping_district' => 'District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',
            'shipping_method' => 'Kurir',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',
            'payment_method' => 'VA',
            'va_bank' => 'MANDIRI',
        ]);

        $response
            ->assertRedirect(route('cart.index'));
    }
    public function test_checkout_pickup_special_batch_saves_selected_fulfillment_date(): void
    {
        $variant = ProductVariant::query()
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->firstOrFail();

        $variant->update([
            'weight' => 250,
        ]);

        Inventory::query()
            ->where('product_variant_id', $variant->id)
            ->update([
                'stock' => 10,
            ]);

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => (float) $variant->price,
                'qty' => 1,
                'custom_name' => '',
                'custom_number' => '',
            ],
        ]);

        $this->mock(BiteshipService::class)
            ->shouldReceive('getCourierRates')
            ->never();

        $this->mockDokuSuccess();

        $response = $this->post(route('checkout.store'), [
            'name' => 'Special Pickup Customer',
            'email' => 'special-pickup@test.com',
            'phone' => '08123456783',
            'shipping_address' => 'Alamat Pickup',
            'shipping_district' => 'District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',

            'shipping_method' => 'Ambil di Tempat',

            'pickup_date' => '2026-10-31',
            'pickup_time_start' => '09:00',
            'pickup_time_end' => '11:00',

            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',

            'payment_method' => 'VA',
            'va_bank' => 'MANDIRI',
        ]);

        $response->assertRedirect(route('checkout.success'));

        $transaction = Transaction::query()
            ->where('shipping_email', 'special-pickup@test.com')
            ->firstOrFail();

        $this->assertSame(
            '2026-10-31',
            $transaction->fulfillment_date->toDateString()
        );

        $this->assertSame(
            '2026-10-31',
            $transaction->pickup_date->toDateString()
        );

        $this->assertSame(
            0,
            (int) FulfillmentSlot::query()
                ->whereDate('date', '2026-10-31')
                ->value('used_count')
        );

        $slotId = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-31')
            ->value('id');

        $this->assertDatabaseHas('fulfillment_holds', [
            'transaction_id' => $transaction->id,
            'fulfillment_slot_id' => $slotId,
            'status' => FulfillmentHold::HELD,
        ]);
    }
    public function test_checkout_pickup_rejects_when_selected_fulfillment_date_is_full(): void
    {
        $variant = ProductVariant::query()
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->firstOrFail();

        $variant->update([
            'weight' => 250,
        ]);

        Inventory::query()
            ->where('product_variant_id', $variant->id)
            ->update([
                'stock' => 10,
            ]);

        FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->update([
                'capacity' => 100,
                'used_count' => 100,
            ]);

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => (float) $variant->price,
                'qty' => 1,
                'custom_name' => '',
                'custom_number' => '',
            ],
        ]);

        $this->mock(BiteshipService::class)
            ->shouldReceive('getCourierRates')
            ->never();

        $response = $this->post(route('checkout.store'), [
            'name' => 'Full Slot Customer',
            'email' => 'full-slot@test.com',
            'phone' => '08123456784',
            'shipping_address' => 'Alamat Pickup',
            'shipping_district' => 'District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',

            'shipping_method' => 'Ambil di Tempat',

            'pickup_date' => '2026-10-30',
            'pickup_time_start' => '09:00',
            'pickup_time_end' => '11:00',

            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',

            'payment_method' => 'VA',
            'va_bank' => 'MANDIRI',
        ]);

        $response->assertSessionHasErrors('pickup_date');

        $this->assertDatabaseMissing('transactions', [
            'shipping_email' => 'full-slot@test.com',
        ]);

        $this->assertSame(
            100,
            (int) FulfillmentSlot::query()
                ->whereDate('date', '2026-10-30')
                ->value('used_count')
        );
    }
    public function test_checkout_courier_before_special_batch_saves_earliest_fulfillment_date(): void
    {
        $variant = ProductVariant::query()
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->firstOrFail();

        $variant->update([
            'weight' => 250,
        ]);

        Carbon::setTestNow(
            Carbon::parse('2026-10-20 10:00:00')
        );

        FulfillmentSlot::query()
            ->whereBetween('date', [
                '2026-10-30',
                '2026-11-03',
            ])
            ->update([
                'capacity' => 100,
                'used_count' => 0,
            ]);

        FulfillmentHold::query()->delete();

        Inventory::query()
            ->where('product_variant_id', $variant->id)
            ->update([
                'stock' => 10,
            ]);

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => (float) $variant->price,
                'qty' => 1,
                'custom_name' => '',
                'custom_number' => '',
            ],
        ]);

        $this->mock(BiteshipService::class, function ($mock) {
            $mock->shouldReceive('getCourierRates')
                ->once()
                ->andReturn([
                    'success' => true,
                    'rates' => [[
                        'courier_code' => 'jnt',
                        'courier_name' => 'J&T',
                        'service_code' => 'ez',
                        'service_name' => 'EZ',
                        'price' => 8000,
                        'duration' => '2-3 days',
                        'service_type' => 'standard',
                        'shipping_type' => 'parcel',
                    ]],
                    'raw' => [],
                ]);
        });

        $this->mockDokuSuccess();

        $response = $this->post(route('checkout.store'), [
            'name' => 'Special Courier Customer',
            'email' => 'special-courier@test.com',
            'phone' => '08123456784',
            'shipping_address' => 'Alamat Kurir',
            'shipping_district' => 'District',
            'shipping_city' => 'Banjarmasin',
            'shipping_province' => 'Kalimantan Selatan',
            'shipping_postal_code' => '70111',

            'shipping_method' => 'Kurir',
            'courier_code' => 'jnt',
            'courier_service_code' => 'ez',

            'payment_method' => 'VA',
            'va_bank' => 'MANDIRI',
        ]);

        $response->assertRedirect(route('checkout.success'));

        $transaction = Transaction::query()
            ->where('shipping_email', 'special-courier@test.com')
            ->firstOrFail();

        $this->assertSame(
            '2026-10-30',
            $transaction->fulfillment_date->toDateString()
        );

        $this->assertNull($transaction->pickup_date);

        $this->assertSame(
            0,
            (int) FulfillmentSlot::query()
                ->whereDate('date', '2026-10-30')
                ->value('used_count')
        );

        $slotId = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->value('id');

        $this->assertDatabaseHas('fulfillment_holds', [
            'transaction_id' => $transaction->id,
            'fulfillment_slot_id' => $slotId,
            'status' => FulfillmentHold::HELD,
        ]);

        Carbon::setTestNow();
    }

    public function test_fulfillment_availability_returns_available_for_open_special_batch_date(): void
    {
        FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->update([
                'capacity' => 100,
                'used_count' => 0,
            ]);

        $response = $this->getJson(
            route('checkout.fulfillment-availability', [
                'date' => '2026-10-30',
            ])
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'available' => true,
                'date' => '2026-10-30',
            ]);
    }
    public function test_fulfillment_availability_returns_unavailable_when_special_batch_date_is_full(): void
    {
        FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->update([
                'capacity' => 100,
                'used_count' => 100,
            ]);

        $response = $this->getJson(
            route('checkout.fulfillment-availability', [
                'date' => '2026-10-30',
            ])
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'available' => false,
                'date' => '2026-10-30',
            ])
            ->assertJsonPath(
                'message',
                'Tanggal pickup tersebut sudah penuh. Silakan pilih tanggal lain.'
            );
    }
}
