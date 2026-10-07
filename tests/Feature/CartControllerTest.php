<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function activeVariant(): ProductVariant
    {
        $variant = ProductVariant::query()
            ->with('product')
            ->whereHas('product', function ($query) {
                $query->where('status', true);
            })
            ->firstOrFail();

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

    private function addPayload(ProductVariant $variant, array $overrides = []): array
    {
        return array_merge([
            'variant_id' => $variant->id,
            'qty' => 1,
            'custom_name' => '',
            'custom_number' => '',
        ], $overrides);
    }

    public function test_custom_off_without_custom_input_has_no_fee(): void
    {
        $variant = $this->activeVariant();

        $variant->product->update([
            'customization_enabled' => false,
            'customization_price' => 150000,
        ]);

        $response = $this->post(
            route('cart.add'),
            $this->addPayload($variant)
        );

        $response->assertRedirect();

        $cart = session('cart');

        $this->assertCount(1, $cart);

        $item = array_values($cart)[0];

        $this->assertSame(
            (float) $variant->price,
            (float) $item['price']
        );

        $this->assertSame('', $item['custom_name']);
        $this->assertSame('', $item['custom_number']);
    }

    public function test_custom_on_without_custom_input_has_no_fee(): void
    {
        $variant = $this->activeVariant();

        $variant->product->update([
            'customization_enabled' => true,
            'customization_price' => 150000,
        ]);

        $response = $this->post(
            route('cart.add'),
            $this->addPayload($variant)
        );

        $response->assertRedirect();

        $cart = session('cart');

        $this->assertCount(1, $cart);

        $item = array_values($cart)[0];

        $this->assertSame(
            (float) $variant->price,
            (float) $item['price']
        );
    }

    public function test_custom_on_with_name_applies_database_fee_once(): void
    {
        $variant = $this->activeVariant();

        $variant->product->update([
            'customization_enabled' => true,
            'customization_price' => 150000,
        ]);

        $response = $this->post(
            route('cart.add'),
            $this->addPayload($variant, [
                'custom_name' => 'MESSI',
            ])
        );

        $response->assertRedirect();

        $cart = session('cart');

        $this->assertCount(1, $cart);

        $item = array_values($cart)[0];

        $expectedPrice = (float) $variant->price + 150000;

        $this->assertSame(
            $expectedPrice,
            (float) $item['price']
        );

        $this->assertSame('MESSI', $item['custom_name']);
        $this->assertSame('', $item['custom_number']);
    }

    public function test_custom_on_with_name_and_number_applies_database_fee_only_once(): void
    {
        $variant = $this->activeVariant();

        $variant->product->update([
            'customization_enabled' => true,
            'customization_price' => 150000,
        ]);

        $response = $this->post(
            route('cart.add'),
            $this->addPayload($variant, [
                'custom_name' => 'MESSI',
                'custom_number' => '10',
            ])
        );

        $response->assertRedirect();

        $cart = session('cart');

        $this->assertCount(1, $cart);

        $item = array_values($cart)[0];

        $expectedPrice = (float) $variant->price + 150000;

        $this->assertSame(
            $expectedPrice,
            (float) $item['price']
        );

        $this->assertSame('MESSI', $item['custom_name']);
        $this->assertSame('10', $item['custom_number']);
    }

    public function test_custom_off_with_custom_input_is_rejected(): void
    {
        $variant = $this->activeVariant();

        $variant->product->update([
            'customization_enabled' => false,
            'customization_price' => 150000,
        ]);

        $response = $this->post(
            route('cart.add'),
            $this->addPayload($variant, [
                'custom_name' => 'MESSI',
                'custom_number' => '10',
            ])
        );

        $response->assertRedirect();

        $response->assertSessionHas(
            'error',
            'Produk ini tidak menyediakan custom nama atau nomor.'
        );

        $this->assertEmpty(session('cart', []));
    }
}