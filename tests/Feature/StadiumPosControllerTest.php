<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StadiumPosControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function createStadiumProduct(int $stock = 10): ProductVariant
    {
        $product = Product::create([
            'product_code' => 'STADIUM-TEST-' . uniqid(),
            'category_id' => 6,
            'name' => 'Produk Test Stadion',
            'slug' => 'produk-test-stadion-' . uniqid(),
            'description' => null,
            'material' => null,
            'status' => 1,
            'availability' => 'ready',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'size_id' => 1,
            'color_id' => null,
            'sku' => 'STADIUM-SKU-' . uniqid(),
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

    private function assertStadiumInvoice(string $invoice): void
    {
        $this->assertMatchesRegularExpression(
            '/^STADIUM-\d{8}-[A-Z]{6}$/',
            $invoice
        );
    }

    public function test_stadium_pos_page_is_available(): void
    {
        $this->loginAsSuperAdmin();

        $this->get(route('admin.kasir-stadion'))
            ->assertOk()
            ->assertSee('Kasir Stadion')
            ->assertSee('value="TRANSFER"', false)
            ->assertSee('value="QRIS"', false)
            ->assertSee(route('admin.stadium-transactions.index'), false);
    }

    public function test_stadium_pos_can_update_variant_stock(): void
    {
        $variant = $this->createStadiumProduct(5);
        $this->loginAsSuperAdmin();

        $this->postJson(route('admin.kasir-stadion.update-stock'), [
            'variant_id' => $variant->id,
            'stock' => 12,
        ])
            ->assertOk()
            ->assertJsonPath('variant_id', $variant->id)
            ->assertJsonPath('stock', 12);

        $this->assertSame(
            12,
            (int) Inventory::where(
                'product_variant_id',
                $variant->id
            )->value('stock')
        );
    }

    public function test_cash_checkout_creates_stadium_transaction_with_stadium_invoice(): void
    {
        $variant = $this->createStadiumProduct();
        $this->loginAsSuperAdmin();

        $response = $this->postJson(route('admin.kasir-stadion.checkout'), [
            'payment_method' => 'CASH',
            'discount' => 0,
            'items' => [
                ['id' => $variant->id, 'qty' => 1],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('payment_method', 'CASH')
            ->assertJsonPath('status', Transaction::ORDER_COMPLETED)
            ->assertJsonPath('redirect', route('admin.stadium-transactions.index'));

        $transaction = Transaction::query()
            ->where('invoice_number', $response->json('invoice_number'))
            ->firstOrFail();

        $this->assertStadiumInvoice($transaction->invoice_number);
        $this->assertSame(Transaction::SOURCE_STADIUM, $transaction->source);
        $this->assertSame(Transaction::ORDER_COMPLETED, $transaction->status);
        $this->assertNotNull($transaction->paid_at);
        $this->assertEquals(150000, (float) $transaction->total);

        $this->assertSame(
            9,
            (int) Inventory::where(
                'product_variant_id',
                $variant->id
            )->value('stock')
        );

        $this->assertSame(
            1,
            StockMovement::query()
                ->where('transaction_id', $transaction->id)
                ->count()
        );

        $this->assertSame(
            2,
            OrderStatusHistory::query()
                ->where('transaction_id', $transaction->id)
                ->count()
        );

        $this->assertDatabaseHas('order_status_histories', [
            'transaction_id' => $transaction->id,
            'status' => Transaction::PAYMENT_CONFIRMED,
        ]);

        $this->assertDatabaseHas('order_status_histories', [
            'transaction_id' => $transaction->id,
            'status' => Transaction::ORDER_COMPLETED,
        ]);
    }

    public function test_stadium_transaction_appears_only_on_stadium_transaction_page(): void
    {
        $variant = $this->createStadiumProduct();
        $this->loginAsSuperAdmin();

        $response = $this->postJson(route('admin.kasir-stadion.checkout'), [
            'payment_method' => 'QRIS',
            'discount' => 0,
            'items' => [
                ['id' => $variant->id, 'qty' => 1],
            ],
        ]);

        $response->assertOk();

        $invoice = $response->json('invoice_number');

        // Halaman stadion menampilkan transaksi baru.
        $this->get(route('admin.stadium-transactions.index'))
            ->assertOk()
            ->assertSee($invoice);

        // Halaman Kasir reguler tidak boleh menampilkan transaksi stadion.
        // Tidak memakai parameter search karena input pencarian akan
        // menggemakan kata kunci ke dalam markup halaman.
        $this->get(route('admin.kasir-transactions.index'))
            ->assertOk()
            ->assertDontSee($invoice);
    }

    public function test_stadium_checkout_invoices_are_unique(): void
    {
        $variant = $this->createStadiumProduct();
        $this->loginAsSuperAdmin();

        $invoices = [];

        foreach (range(1, 3) as $attempt) {
            $response = $this->postJson(route('admin.kasir-stadion.checkout'), [
                'payment_method' => 'CASH',
                'discount' => 0,
                'items' => [
                    ['id' => $variant->id, 'qty' => 1],
                ],
            ]);

            $response->assertOk();

            $invoices[] = $response->json('invoice_number');
        }

        $this->assertCount(3, array_unique($invoices));

        foreach ($invoices as $invoice) {
            $this->assertStadiumInvoice($invoice);
            $this->assertSame(
                1,
                Transaction::query()
                    ->where('invoice_number', $invoice)
                    ->count()
            );
        }
    }

    public function test_regular_kasir_checkout_keeps_kasir_prefix_and_pos_source(): void
    {
        $variant = $this->createStadiumProduct();
        $this->loginAsSuperAdmin();

        $response = $this->postJson(route('admin.kasir.checkout'), [
            'payment_method' => 'CASH',
            'discount' => 0,
            'items' => [
                ['id' => $variant->id, 'qty' => 1],
            ],
        ]);

        $response->assertOk();

        $transaction = Transaction::query()
            ->where('invoice_number', $response->json('invoice_number'))
            ->firstOrFail();

        $this->assertMatchesRegularExpression(
            '/^KASIR-\d{8}-[A-Z0-9]{6}$/',
            $transaction->invoice_number
        );
        $this->assertSame(Transaction::SOURCE_POS, $transaction->source);
        $this->assertStringStartsWith('KASIR-', $transaction->invoice_number);
    }
}
