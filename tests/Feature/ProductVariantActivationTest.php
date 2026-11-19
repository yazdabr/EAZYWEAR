<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductVariantActivationTest extends TestCase
{
    use DatabaseTransactions;

    private function superAdmin(): User
    {
        return User::factory()->create([
            'role' => 'super_admin',
        ]);
    }

    /**
     * @return array{0: Product, 1: ProductVariant, 2: ProductVariant, 3: Size, 4: Size, 5: Inventory, 6: Inventory}
     */
    private function createProductWithTwoSizes(): array
    {
        $sizes = Size::query()->orderBy('id')->take(2)->get();

        $this->assertCount(2, $sizes, 'Butuh minimal dua ukuran pada database testing.');

        [$sizeA, $sizeB] = [$sizes[0], $sizes[1]];

        $product = Product::query()->create([
            'product_code' => 'ACT-TEST-' . Str::upper(Str::random(8)),
            'category_id' => Category::query()->value('id'),
            'name' => 'Activation Test ' . Str::upper(Str::random(6)),
            'slug' => 'activation-test-' . Str::lower(Str::random(10)),
            'description' => null,
            'material' => null,
            'availability' => 'ready',
            'pre_order_closed' => false,
            'ready_stock_closed' => false,
            'status' => true,
            'customization_enabled' => false,
            'customization_price' => 0,
            'longsleeve_enabled' => false,
            'longsleeve_price' => 0,
            'patch_enabled' => false,
            'patch_price' => 0,
        ]);

        ProductImage::query()->create([
            'product_id' => $product->id,
            'image' => 'products/activation-test.jpg',
            'is_thumbnail' => true,
            'sort_order' => 1,
        ]);

        $variantA = ProductVariant::query()->create([
            'product_id' => $product->id,
            'size_id' => $sizeA->id,
            'color_id' => null,
            'sku' => 'ACT-SKU-' . Str::upper(Str::random(10)),
            'price' => 150000,
            'weight' => 500,
        ]);

        $variantB = ProductVariant::query()->create([
            'product_id' => $product->id,
            'size_id' => $sizeB->id,
            'color_id' => null,
            'sku' => 'ACT-SKU-' . Str::upper(Str::random(10)),
            'price' => 175000,
            'weight' => 500,
        ]);

        $inventoryA = Inventory::query()->create([
            'product_variant_id' => $variantA->id,
            'stock' => 10,
        ]);

        $inventoryB = Inventory::query()->create([
            'product_variant_id' => $variantB->id,
            'stock' => 10,
        ]);

        return [$product, $variantA, $variantB, $sizeA, $sizeB, $inventoryA, $inventoryB];
    }

    private function updateSizes(User $admin, Product $product, array $variants)
    {
        $sizeIds = array_map('intval', array_keys($variants));

        return $this->actingAs($admin)->putJson(
            route('admin.products.update', $product),
            [
                'product_code' => $product->product_code,
                'category_id' => $product->category_id,
                'name' => $product->name,
                'description' => $product->description,
                'material' => $product->material,
                'availability' => $product->availability,
                'pre_order_closed' => (int) $product->pre_order_closed,
                'ready_stock_closed' => (int) $product->ready_stock_closed,
                'status' => (int) $product->status,
                'customization_enabled' => (int) $product->customization_enabled,
                'customization_price' => (int) $product->customization_price,
                'longsleeve_enabled' => (int) $product->longsleeve_enabled,
                'longsleeve_price' => (int) $product->longsleeve_price,
                'patch_enabled' => (int) $product->patch_enabled,
                'patch_price' => (int) $product->patch_price,
                'size_ids' => $sizeIds,
                'variants' => $variants,
                'existing_images' => $product->images()->pluck('id')->map(fn ($id) => (string) $id)->all(),
            ]
        );
    }

    private function variantPayload(array $variants): array
    {
        $payload = [];

        foreach ($variants as $sizeId => $variant) {
            $payload[$sizeId] = [
                'price' => $variant['price'],
                'stock' => $variant['stock'],
                'weight' => $variant['weight'] ?? 500,
            ];
        }

        return $payload;
    }

    public function test_variant_with_transaction_history_is_deactivated_not_deleted(): void
    {
        $admin = $this->superAdmin();

        [$product, $variantA, $variantB, $sizeA, $sizeB, $inventoryA] =
            $this->createProductWithTwoSizes();

        $transaction = Transaction::factory()->create();

        $item = $transaction->items()->create([
            'product_variant_id' => $variantA->id,
            'availability' => 'ready',
            'custom_name' => null,
            'custom_number' => null,
            'qty' => 1,
            'price' => 150000,
            'subtotal' => 150000,
        ]);

        $movement = StockMovement::query()->create([
            'inventory_id' => $inventoryA->id,
            'transaction_id' => $transaction->id,
            'transaction_item_id' => $item->id,
            'type' => 'OUT',
            'qty' => 1,
            'stock_before' => 10,
            'stock_after' => 9,
            'description' => 'Test activation history',
        ]);

        $response = $this->updateSizes($admin, $product, $this->variantPayload([
            $sizeB->id => [
                'price' => 175000,
                'stock' => 10,
                'weight' => 500,
            ],
        ]));

        $response->assertOk();

        $this->assertDatabaseHas('product_variants', [
            'id' => $variantA->id,
            'is_active' => 0,
        ]);

        $this->assertDatabaseHas('product_variants', [
            'id' => $variantB->id,
            'is_active' => 1,
        ]);

        // Riwayat transaksi, inventory, dan stock movement tidak boleh hilang.
        $this->assertDatabaseHas('transaction_items', ['id' => $item->id]);
        $this->assertDatabaseHas('inventories', ['id' => $inventoryA->id]);
        $this->assertDatabaseHas('stock_movements', ['id' => $movement->id]);
    }

    public function test_reactivating_variant_reuses_the_existing_record(): void
    {
        $admin = $this->superAdmin();

        [$product, $variantA, $variantB, $sizeA, $sizeB] =
            $this->createProductWithTwoSizes();

        $this->updateSizes($admin, $product, $this->variantPayload([
            $sizeB->id => [
                'price' => 175000,
                'stock' => 10,
                'weight' => 500,
            ],
        ]))->assertOk();

        $this->assertDatabaseHas('product_variants', [
            'id' => $variantA->id,
            'is_active' => 0,
        ]);

        $this->updateSizes($admin, $product, $this->variantPayload([
            $sizeA->id => [
                'price' => 160000,
                'stock' => 7,
                'weight' => 500,
            ],
            $sizeB->id => [
                'price' => 175000,
                'stock' => 10,
                'weight' => 500,
            ],
        ]))->assertOk();

        $variantA->refresh();

        $this->assertTrue($variantA->is_active);
        $this->assertSame(160000.0, (float) $variantA->price);

        $this->assertSame(
            1,
            ProductVariant::query()
                ->where('product_id', $product->id)
                ->where('size_id', $sizeA->id)
                ->count(),
            'Variant lama harus digunakan kembali, bukan membuat duplikat.'
        );

        $this->assertSame(
            $variantA->id,
            ProductVariant::query()
                ->where('product_id', $product->id)
                ->where('size_id', $sizeA->id)
                ->value('id')
        );

        $this->assertSame(
            7,
            (int) Inventory::query()
                ->where('product_variant_id', $variantA->id)
                ->value('stock')
        );
    }

    public function test_inactive_variant_is_hidden_from_admin_form_pos_catalog_and_meta_feed(): void
    {
        $admin = $this->superAdmin();

        [$product, $variantA, $variantB, $sizeA, $sizeB] =
            $this->createProductWithTwoSizes();

        $this->updateSizes($admin, $product, $this->variantPayload([
            $sizeB->id => [
                'price' => 175000,
                'stock' => 10,
                'weight' => 500,
            ],
        ]))->assertOk();

        $product->refresh();
        $variantA->refresh();
        $variantB->refresh();

        $adminResponse = $this->actingAs($admin)->get(route('admin.products'));
        $adminResponse->assertOk();

        $listedProduct = $adminResponse
            ->viewData('products')
            ->getCollection()
            ->firstWhere('id', $product->id);

        $this->assertNotNull($listedProduct);
        $this->assertArrayNotHasKey((string) $sizeA->id, $listedProduct->variant_data);
        $this->assertArrayHasKey((string) $sizeB->id, $listedProduct->variant_data);
        $this->assertNotContains((int) $sizeA->id, $listedProduct->size_ids);
        $this->assertContains((int) $sizeB->id, $listedProduct->size_ids);

        $posResponse = $this->actingAs($admin)->get(route('admin.kasir'));
        $posResponse->assertOk();

        $posProduct = $posResponse
            ->viewData('products')
            ->firstWhere('id', $product->id);

        $this->assertNotNull($posProduct);
        $this->assertFalse($posProduct->variants->contains('id', $variantA->id));
        $this->assertTrue($posProduct->variants->contains('id', $variantB->id));

        $detailResponse = $this->get(route('product.detail', $product->slug));
        $detailResponse->assertOk();

        $availableSizeIds = collect(
            $detailResponse->viewData('product')->available_sizes
        )->pluck('size_id')->map(fn ($id) => (int) $id)->all();

        $this->assertNotContains((int) $sizeA->id, $availableSizeIds);
        $this->assertContains((int) $sizeB->id, $availableSizeIds);

        $this->assertDatabaseHas('product_variants', [
            'id' => $variantA->id,
            'is_active' => 0,
        ]);

        $loadedProduct = Product::with([
            'variants' => fn ($query) => $query->where('is_active', true),
        ])->find($product->id);

        $this->assertFalse(
            $loadedProduct->variants->contains('id', $variantA->id),
            'Variant nonaktif masih ikut pada eager load variants aktif.'
        );

        $feedResponse = $this->get(route('meta.product-feed'));
        $feedResponse->assertOk();
        $feedResponse->assertSee($variantB->sku);
        $feedResponse->assertDontSee($variantA->sku);
    }

    public function test_inactive_variant_cannot_be_used_in_new_booth_or_cart_transaction(): void
    {
        $admin = $this->superAdmin();

        [$product, $variantA, $variantB, $sizeA, $sizeB] =
            $this->createProductWithTwoSizes();

        $this->updateSizes($admin, $product, $this->variantPayload([
            $sizeB->id => [
                'price' => 175000,
                'stock' => 10,
                'weight' => 500,
            ],
        ]))->assertOk();

        $transactionCount = Transaction::query()->count();

        $boothResponse = $this->actingAs($admin)->postJson(
            route('admin.kasir.checkout'),
            [
                'payment_method' => 'CASH',
                'discount' => 0,
                'items' => [
                    ['id' => $variantA->id, 'qty' => 1],
                ],
            ]
        );

        $boothResponse->assertStatus(422);
        $boothResponse->assertJsonValidationErrors('items');
        $this->assertSame($transactionCount, Transaction::query()->count());

        $cartResponse = $this->post(route('cart.add'), [
            'variant_id' => $variantA->id,
            'qty' => 1,
        ]);

        $cartResponse->assertRedirect();
        $cartResponse->assertSessionHas('error');
        $cartResponse->assertSessionMissing('cart');
    }
}
