<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DokuQrisService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class BoothPosControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function createBoothProduct(int $stock = 10): ProductVariant
    {
        $product = Product::create([
            'product_code' => 'BOOTH-TEST-' . uniqid(),
            'category_id' => 6,
            'name' => 'Produk Test Booth',
            'slug' => 'produk-test-booth-' . uniqid(),
            'description' => null,
            'material' => null,
            'status' => 1,
            'availability' => 'ready',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size_id' => 1,
            'color_id' => null,
            'sku' => 'BOOTH-SKU-' . uniqid(),
            'price' => 150000,
            'weight' => 500,
        ]);

        Inventory::create([
            'product_variant_id' => $variant->id,
            'stock' => $stock,
        ]);

        return $variant;
    }

    private function loginAsSuperAdmin(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'super_admin',
        ]));
    }

    public function test_qris_checkout_stays_pending_and_does_not_reduce_stock(): void
    {
        $variant = $this->createBoothProduct();
        $this->loginAsSuperAdmin();

        $qrContent = '00020101021226670016COM.DOKU.WWW01189360091500000000000215QRIS2026100614300303UMI5204599953033605802ID5908Eazywear6007Banjarmasin6105701116304ABCD';

        $doku = Mockery::mock(DokuQrisService::class);
        $doku->shouldReceive('generateQr')
            ->once()
            ->andReturnUsing(function ($invoice, $amount, $validUntil) use ($qrContent) {
                return [
                    'responseCode' => '2004700',
                    'referenceNo' => 'DOKU-TEST-REF-001',
                    'partnerReferenceNo' => $invoice,
                    'qrContent' => $qrContent,
                ];
            });

        $this->app->instance(DokuQrisService::class, $doku);

        $response = $this->postJson(route('admin.kasir.checkout'), [
            'payment_method' => 'QRIS',
            'discount' => 0,
            'items' => [
                ['id' => $variant->id, 'qty' => 1],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('payment_method', 'QRIS')
            ->assertJsonPath('status', 'PENDING')
            ->assertJsonPath('qris_reference_no', 'DOKU-TEST-REF-001');

        $transaction = Transaction::query()
            ->where('invoice_number', $response->json('invoice_number'))
            ->firstOrFail();

        $this->assertSame('QRIS', $transaction->payment_method);
        $this->assertSame('PENDING', $transaction->status);
        $this->assertSame(
            'DOKU-TEST-REF-001',
            $transaction->qris_reference_no
        );
        $this->assertNotEmpty($response->json('qris_qr_code'));

        $this->assertSame(
            10,
            (int) Inventory::where(
                'product_variant_id',
                $variant->id
            )->value('stock')
        );
    }

    public function test_cash_checkout_marks_paid_and_reduces_stock(): void
    {
        $variant = $this->createBoothProduct();
        $this->loginAsSuperAdmin();

        $doku = Mockery::mock(DokuQrisService::class);
        $doku->shouldNotReceive('generateQr');

        $this->app->instance(DokuQrisService::class, $doku);

        $response = $this->postJson(route('admin.kasir.checkout'), [
            'payment_method' => 'CASH',
            'discount' => 0,
            'items' => [
                ['id' => $variant->id, 'qty' => 1],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('payment_method', 'CASH')
            ->assertJsonPath('status', 'PAID');

        $transaction = Transaction::query()
            ->where('invoice_number', $response->json('invoice_number'))
            ->firstOrFail();

        $this->assertSame('PAID', $transaction->status);
        $this->assertNotNull($transaction->paid_at);

        $this->assertSame(
            9,
            (int) Inventory::where(
                'product_variant_id',
                $variant->id
            )->value('stock')
        );
    }
}