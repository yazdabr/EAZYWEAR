<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Services\DokuService;
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

    public function test_checkout_creates_pending_transaction_with_va(): void
    {
        $variant = ProductVariant::query()
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->firstOrFail();

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
                'custom_name' => 'MESSI',
                'custom_number' => '10',
            ],
        ]);

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
            'payment_method' => 'VA',
        ]);

        $response
            ->assertRedirect(route('checkout.success'));

        $this->assertDatabaseHas('transactions', [
            'status' => 'PENDING',
            'va_number' => '190089123456789012',
            'payment_method' => 'VA',
        ]);
    }

    public function test_checkout_fails_when_doku_create_va_failed(): void
    {
        $variant = ProductVariant::query()
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->firstOrFail();

        Session::put('cart', [
            [
                'variant_id' => $variant->id,
                'price' => $variant->price,
                'qty' => 1,
                'custom_name' => 'TEST',
                'custom_number' => '10',
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
            'payment_method' => 'VA',
        ]);

        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('transactions', [
            'payment_method' => 'VA',
            'shipping_email' => 'customer@test.com',
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
            'payment_method' => 'VA',
        ]);

        $response
            ->assertRedirect(route('cart.index'));
    }
}