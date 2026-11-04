<?php

namespace Tests\Feature;

use App\Mail\OrderShippedMail;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionShipment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderShippedMailTest extends TestCase
{
    use DatabaseTransactions;

    public function test_legacy_transaction_tracking_and_personalization_are_rendered(): void
    {
        $transaction = Transaction::factory()->create([
            'courier' => 'J&T Express',
            'tracking_number' => 'JO1234567890',
            'biteship_waybill_id' => 'BITES123456',
        ]);

        $this->createPersonalizedItem($transaction);

        $html = (new OrderShippedMail($transaction))->render();

        $this->assertStringContainsString('JO1234567890', $html);
        $this->assertStringContainsString('J&amp;T Express', $html);
        $this->assertStringContainsString('Test Jersey', $html);
        $this->assertStringContainsString('AWANG', $html);
        $this->assertStringContainsString('17', $html);
        $this->assertStringContainsString('PATCH', $html);
        $this->assertStringContainsString('LONGSLEEVE', $html);
    }

    public function test_shipment_uses_transaction_tracking_as_fallback(): void
    {
        $transaction = Transaction::factory()->create([
            'courier' => 'J&T Express',
            'tracking_number' => 'JO0987654321',
        ]);

        TransactionShipment::create([
            'transaction_id' => $transaction->id,
            'shipping_method' => 'Regular',
            'courier' => 'J&T Express',
            'tracking_number' => null,
            'biteship_waybill_id' => null,
        ]);

        $html = (new OrderShippedMail($transaction))->render();

        $this->assertStringContainsString('JO0987654321', $html);
    }

    public function test_shipment_tracking_number_takes_precedence(): void
    {
        $transaction = Transaction::factory()->create([
            'tracking_number' => 'OLD-TRACKING-123',
        ]);

        TransactionShipment::create([
            'transaction_id' => $transaction->id,
            'shipping_method' => 'Regular',
            'tracking_number' => 'NEW-TRACKING-456',
        ]);

        $html = (new OrderShippedMail($transaction))->render();

        $this->assertStringContainsString('NEW-TRACKING-456', $html);
    }

    private function createPersonalizedItem(Transaction $transaction): void
    {
        $now = now();
        $suffix = Str::uuid()->toString();

        $categoryId = DB::table('categories')->insertGetId([
            'name' => 'Test Category',
            'slug' => 'test-category-' . $suffix,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'name' => 'Test Jersey',
            'slug' => 'test-jersey-' . $suffix,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $sizeId = DB::table('sizes')->insertGetId([
            'name' => 'L',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $variantId = DB::table('product_variants')->insertGetId([
            'product_id' => $productId,
            'size_id' => $sizeId,
            'sku' => 'TEST-' . strtoupper(Str::random(12)),
            'price' => 100000,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_variant_id' => $variantId,
            'custom_name' => 'awang',
            'custom_number' => '17',
            'qty' => 1,
            'price' => 100000,
            'subtotal' => 100000,
            'weight' => 200,
            'is_patch' => true,
            'patch_price' => 10000,
            'is_longsleeve' => true,
            'longsleeve_price' => 15000,
        ]);
    }
}
