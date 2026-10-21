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
            'longsleeve_enabled' => 0,
            'longsleeve_price' => 0,

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
            'longsleeve_enabled' => 0,
            'longsleeve_price' => 0,
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
            'longsleeve_enabled' => 0,
            'longsleeve_price' => 0,
        ]);
    }

    public function test_admin_can_create_product_with_customization_disabled(): void
    {
        $user = $this->superAdmin();

        $payload = $this->storePayload([
            'customization_enabled' => 0,
            'customization_price' => 150000,
            'longsleeve_enabled' => 0,
            'longsleeve_price' => 0,
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
            'longsleeve_enabled' => 0,
            'longsleeve_price' => 0,
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

    public function test_admin_can_save_closed_pre_order_status(): void
    {
        $user = $this->superAdmin();

        $response = $this->actingAs($user)->postJson(
            route('admin.products.store'),
            $this->storePayload([
                'availability' => 'pre_order',
                'pre_order_closed' => 1,
            ])
        );

        $response->assertCreated();

        $this->assertDatabaseHas('products', [
            'id' => $response->json('data.id'),
            'availability' => 'pre_order',
            'pre_order_closed' => 1,
        ]);
    }

    public function test_ready_stock_cannot_keep_pre_order_closed_flag(): void
    {
        $user = $this->superAdmin();

        $response = $this->actingAs($user)->postJson(
            route('admin.products.store'),
            $this->storePayload([
                'availability' => 'ready',
                'pre_order_closed' => 1,
            ])
        );

        $response->assertCreated();

        $this->assertDatabaseHas('products', [
            'id' => $response->json('data.id'),
            'availability' => 'ready',
            'pre_order_closed' => 0,
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
    public function test_product_upload_generates_webp_thumbnail(): void
    {
        if (!function_exists('imagewebp')) {
            $this->markTestSkipped('GD WebP tidak tersedia.');
        }

        \Illuminate\Support\Facades\Storage::fake('public');

        $image = \Illuminate\Http\UploadedFile::fake()->image('jersey.png', 800, 600);

        $response = $this->actingAs($this->superAdmin())->postJson(
            route('admin.products.store'),
            $this->storePayload(['image' => [$image]])
        );

        $response->assertCreated();

        $productId = $response->json('data.id');
        $this->assertNotNull($productId);

        $productImage = \App\Models\ProductImage::where('product_id', $productId)->firstOrFail();
        $originalPath = $productImage->image;
        $thumbnailPath = \App\Support\ProductImageThumbnail::pathFor($originalPath);

        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($originalPath);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($thumbnailPath);
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_product_upload_succeeds_when_thumbnail_generation_fails(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $image = \Illuminate\Http\UploadedFile::fake()->image('jersey.png', 800, 600);

        \Mockery::mock('alias:App\Support\ProductImageThumbnail')
            ->shouldReceive('generate')
            ->once()
            ->andThrow(new \RuntimeException('Simulated thumbnail failure'));

        $response = $this->actingAs($this->superAdmin())->postJson(
            route('admin.products.store'),
            $this->storePayload(['image' => [$image]])
        );

        $response->assertCreated();

        $productId = $response->json('data.id');
        $this->assertNotNull($productId);

        $productImage = \App\Models\ProductImage::where('product_id', $productId)->firstOrFail();
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($productImage->image);
    }
}
