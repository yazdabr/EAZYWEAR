<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Size;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function superAdmin(): User
    {
        return User::factory()->create([
            'role' => 'super_admin',
        ]);
    }

    private function category(): Category
    {
        return Category::query()->firstOrFail();
    }

    private function testSize(): Size
    {
        return Size::query()->firstOrFail();
    }

    private function storePayload(array $overrides = []): array
    {
        $size = $this->testSize();

        return array_replace_recursive([
            'product_code' => 'TEST-CUSTOM-' . uniqid(),
            'category_id' => $this->category()->id,
            'name' => 'Test Custom Jersey',
            'description' => 'Product customization test.',
            'material' => 'Dry-Fit',
            'availability' => 'pre_order',
            'status' => 1,

            'customization_enabled' => 1,
            'customization_price' => 150000,

            'size_ids' => [
                $size->id,
            ],

            'variants' => [
                $size->id => [
                    'price' => 250000,
                    'stock' => 10,
                    'weight' => 500,
                ],
            ],
        ], $overrides);
    }

    public function test_admin_can_create_product_with_customization_enabled(): void
    {
        $user = $this->superAdmin();

        $payload = $this->storePayload([
            'customization_enabled' => 1,
            'customization_price' => 150000,
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson(
                route('admin.products.store'),
                $payload
            );

        $response->assertStatus(201);

        $productId = $response->json('data.id');

        $this->assertNotNull($productId);

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'customization_enabled' => 1,
            'customization_price' => 150000,
        ]);
    }

    public function test_admin_can_create_product_with_customization_disabled(): void
    {
        $user = $this->superAdmin();

        $payload = $this->storePayload([
            'customization_enabled' => 0,
            'customization_price' => 150000,
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson(
                route('admin.products.store'),
                $payload
            );

        $response->assertStatus(201);

        $productId = $response->json('data.id');

        $this->assertNotNull($productId);

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'customization_enabled' => 0,
            'customization_price' => 0,
        ]);
    }

    public function test_admin_can_update_customization_configuration(): void
    {
        $user = $this->superAdmin();

        $product = Product::query()->create([
            'product_code' => 'TEST-UPDATE-' . uniqid(),
            'category_id' => $this->category()->id,
            'name' => 'Test Update Customization',
            'slug' => 'test-update-customization-' . uniqid(),
            'description' => 'Update customization test.',
            'material' => 'Dry-Fit',
            'availability' => 'pre_order',
            'status' => true,
            'customization_enabled' => true,
            'customization_price' => 150000,
        ]);

        $response = $this
            ->actingAs($user)
            ->putJson(
                route('admin.products.update', $product),
                $this->storePayload([
                    'product_code' => $product->product_code,
                    'name' => $product->name,
                    'customization_enabled' => 0,
                    'customization_price' => 999999,
                ])
            );

        $response->assertOk();

        $product->refresh();

        $this->assertFalse((bool) $product->customization_enabled);
        $this->assertSame(0, (int) $product->customization_price);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'customization_enabled' => 0,
            'customization_price' => 0,
        ]);
    }

    public function test_admin_can_update_customization_fee(): void
    {
        $user = $this->superAdmin();

        $product = Product::query()->create([
            'product_code' => 'TEST-FEE-' . uniqid(),
            'category_id' => $this->category()->id,
            'name' => 'Test Customization Fee',
            'slug' => 'test-customization-fee-' . uniqid(),
            'description' => 'Customization fee test.',
            'material' => 'Dry-Fit',
            'availability' => 'pre_order',
            'status' => true,
            'customization_enabled' => true,
            'customization_price' => 75000,
        ]);

        $response = $this
            ->actingAs($user)
            ->putJson(
                route('admin.products.update', $product),
                $this->storePayload([
                    'product_code' => $product->product_code,
                    'name' => $product->name,
                    'customization_enabled' => 1,
                    'customization_price' => 125000,
                ])
            );

        $response->assertOk();

        $product->refresh();

        $this->assertTrue((bool) $product->customization_enabled);
        $this->assertSame(125000, (int) $product->customization_price);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'customization_enabled' => 1,
            'customization_price' => 125000,
        ]);
    }

    public function test_admin_can_create_product_with_ready_stock_availability(): void
    {
        $user = $this->superAdmin();

        $response = $this->actingAs($user)->postJson(
            route('admin.products.store'),
            $this->storePayload(['availability' => 'ready'])
        );

        $response->assertCreated();

        $this->assertDatabaseHas('products', [
            'id' => $response->json('data.id'),
            'availability' => 'ready',
        ]);
    }

    public function test_admin_can_update_product_availability(): void
    {
        $user = $this->superAdmin();

        $product = Product::query()->create([
            'product_code' => 'TEST-AVAIL-' . uniqid(),
            'category_id' => $this->category()->id,
            'name' => 'Test Availability',
            'slug' => 'test-availability-' . uniqid(),
            'description' => 'Availability test.',
            'material' => 'Dry-Fit',
            'availability' => 'pre_order',
            'status' => true,
            'customization_enabled' => false,
            'customization_price' => 0,
        ]);

        $response = $this->actingAs($user)->putJson(
            route('admin.products.update', $product),
            $this->storePayload([
                'product_code' => $product->product_code,
                'name' => $product->name,
                'availability' => 'ready',
            ])
        );

        $response->assertOk();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'availability' => 'ready',
        ]);
    }
}
