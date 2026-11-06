<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
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

    private function assertKasirInvoice(string $invoice): void
    {
        $this->assertMatchesRegularExpression(
            '/^KASIR-\d{8}-[A-Z0-9]{6}$/',
            $invoice
        );
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
            ->assertJsonPath('qris_reference_no', 'DOKU-TEST-REF-001')
            ->assertJsonPath('redirect', route('admin.kasir-transactions.index'));

        $transaction = Transaction::query()
            ->where('invoice_number', $response->json('invoice_number'))
            ->firstOrFail();

        $this->assertKasirInvoice($transaction->invoice_number);
        $this->assertSame('QRIS', $transaction->payment_method);
        $this->assertSame('PENDING', $transaction->status);
        $this->assertSame(Transaction::SOURCE_POS, $transaction->source);
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
            ->assertJsonPath('status', 'ORDER_COMPLETED')
            ->assertJsonPath('redirect', route('admin.kasir-transactions.index'));

        $transaction = Transaction::query()
            ->where('invoice_number', $response->json('invoice_number'))
            ->firstOrFail();

        $this->assertKasirInvoice($transaction->invoice_number);
        $this->assertSame('ORDER_COMPLETED', $transaction->status);
        $this->assertSame(Transaction::SOURCE_POS, $transaction->source);
        $this->assertNotNull($transaction->paid_at);

        $this->assertDatabaseHas('order_status_histories', [
            'transaction_id' => $transaction->id,
            'status' => 'ORDER_COMPLETED',
        ]);

        $this->assertSame(
            9,
            (int) Inventory::where(
                'product_variant_id',
                $variant->id
            )->value('stock')
        );
    }

    public function test_pos_page_shows_transfer_payment_option(): void
    {
        $this->loginAsSuperAdmin();

        $this->get(route('admin.kasir'))
            ->assertOk()
            ->assertSee('value="TRANSFER"', false)
            ->assertSee('Transfer');
    }

    public function test_transfer_checkout_completes_immediately_and_reduces_stock_once(): void
    {
        $variant = $this->createBoothProduct();
        $this->loginAsSuperAdmin();

        $doku = Mockery::mock(DokuQrisService::class);
        $doku->shouldNotReceive('generateQr');

        $this->app->instance(DokuQrisService::class, $doku);

        $response = $this->postJson(route('admin.kasir.checkout'), [
            'payment_method' => 'TRANSFER',
            'discount' => 0,
            'items' => [
                ['id' => $variant->id, 'qty' => 1],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Pembayaran transfer berhasil disimpan.')
            ->assertJsonPath('payment_method', 'TRANSFER')
            ->assertJsonPath('status', Transaction::ORDER_COMPLETED)
            ->assertJsonPath('qris_qr_code', null)
            ->assertJsonPath('qris_reference_no', null)
            ->assertJsonPath('redirect', route('admin.kasir-transactions.index'));

        $transaction = Transaction::query()
            ->where('invoice_number', $response->json('invoice_number'))
            ->firstOrFail();

        $this->assertKasirInvoice($transaction->invoice_number);
        $this->assertSame('TRANSFER', $transaction->payment_method);
        $this->assertSame(Transaction::ORDER_COMPLETED, $transaction->status);
        $this->assertSame(Transaction::SOURCE_POS, $transaction->source);
        $this->assertNotNull($transaction->paid_at);
        $this->assertNull($transaction->qris_reference_no);
        $this->assertNull($transaction->qris_content);

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

        $this->assertDatabaseHas('stock_movements', [
            'transaction_id' => $transaction->id,
            'inventory_id' => Inventory::where(
                'product_variant_id',
                $variant->id
            )->value('id'),
            'type' => 'OUT',
            'qty' => 1,
            'stock_before' => 10,
            'stock_after' => 9,
        ]);

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

        $this->get(route('admin.kasir-transactions.index', [
            'search' => $transaction->invoice_number,
        ]))
            ->assertOk()
            ->assertSee($transaction->invoice_number);

        $this->get(route('admin.kasir-transactions.show', $transaction))
            ->assertOk()
            ->assertSee($transaction->invoice_number)
            ->assertSee('TRANSFER');

        $this->get(route('admin.kasir-transactions.print', $transaction->invoice_number))
            ->assertOk()
            ->assertSee($transaction->invoice_number)
            ->assertSee('TRANSFER');
    }

    public function test_new_pos_invoice_uses_kasir_prefix_and_keeps_legacy_invoice_numbers(): void
    {
        $legacyBooth = Transaction::create([
            'invoice_number' => 'BOOTH-LEGACY-20261105-ABC123',
            'transaction_date' => now()->subDay(),
            'payment_method' => 'CASH',
            'subtotal' => 150000,
            'discount' => 0,
            'shipping' => 0,
            'total' => 150000,
            'status' => 'PAID',
            'source' => Transaction::SOURCE_BOOTH,
        ]);

        $website = Transaction::create([
            'invoice_number' => 'INV-20261105-WEB001',
            'transaction_date' => now()->subDay(),
            'payment_method' => 'VA',
            'subtotal' => 150000,
            'discount' => 0,
            'shipping' => 0,
            'total' => 150000,
            'status' => 'PAID',
            'source' => Transaction::SOURCE_WEBSITE,
        ]);

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

        $response->assertOk();

        $newInvoice = $response->json('invoice_number');

        $this->assertKasirInvoice($newInvoice);
        $this->assertNotSame($legacyBooth->invoice_number, $newInvoice);

        $this->assertSame(
            'BOOTH-LEGACY-20261105-ABC123',
            $legacyBooth->fresh()->invoice_number
        );
        $this->assertSame(
            Transaction::SOURCE_BOOTH,
            $legacyBooth->fresh()->source
        );

        $this->assertSame(
            'INV-20261105-WEB001',
            $website->fresh()->invoice_number
        );
        $this->assertSame(
            Transaction::SOURCE_WEBSITE,
            $website->fresh()->source
        );
    }

    public function test_pos_checkout_invoices_are_unique(): void
    {
        $variant = $this->createBoothProduct();
        $this->loginAsSuperAdmin();

        $doku = Mockery::mock(DokuQrisService::class);
        $doku->shouldNotReceive('generateQr');

        $this->app->instance(DokuQrisService::class, $doku);

        $invoices = [];

        foreach (range(1, 2) as $attempt) {
            $response = $this->postJson(route('admin.kasir.checkout'), [
                'payment_method' => 'CASH',
                'discount' => 0,
                'items' => [
                    ['id' => $variant->id, 'qty' => 1],
                ],
            ]);

            $response->assertOk();

            $invoices[] = $response->json('invoice_number');
        }

        $this->assertCount(2, array_unique($invoices));

        foreach ($invoices as $invoice) {
            $this->assertKasirInvoice($invoice);
            $this->assertSame(
                1,
                Transaction::query()
                    ->where('invoice_number', $invoice)
                    ->count()
            );
        }
    }
}