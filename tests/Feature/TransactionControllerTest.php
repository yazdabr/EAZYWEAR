<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\DokuService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function superAdmin(): User
    {
        return User::factory()->create([
            'role' => 'super_admin',
        ]);
    }

    private function createTestTransaction(int $quantity = 2): Transaction
    {
        $inventory = Inventory::query()
            ->where('stock', '>=', 1)
            ->whereHas(
                'productVariant.product',
                fn ($query) => $query->where('status', true)
            )
            ->firstOrFail();

        $variant = ProductVariant::query()
            ->findOrFail($inventory->product_variant_id);

        $transaction = Transaction::factory()->create([
            'subtotal' => (float) $variant->price * $quantity,
            'total' => (float) $variant->price * $quantity,
            'status' => 'PENDING',
        ]);

        TransactionItem::query()->create([
            'transaction_id' => $transaction->id,
            'product_variant_id' => $variant->id,
            'custom_name' => null,
            'custom_number' => null,
            'qty' => $quantity,
            'price' => $variant->price,
            'subtotal' => (float) $variant->price * $quantity,
        ]);

        return $transaction->load('items');
    }

    public function test_admin_transaction_snapshots_product_variant_weight(): void
    {
        $user = $this->superAdmin();

        $inventory = Inventory::query()
            ->where('stock', '>=', 1)
            ->whereHas(
                'productVariant.product',
                fn ($query) => $query->where('status', true)
            )
            ->firstOrFail();

        $variant = ProductVariant::query()
            ->findOrFail($inventory->product_variant_id);

        $variant->update([
            'weight' => 750,
        ]);

        $this->actingAs($user);

        $this->assertAuthenticatedAs($user);

        $this->assertSame(
            'super_admin',
            $user->role
        );

        $this->withoutExceptionHandling();

        $response = $this->postJson(
            route('admin.transactions.store'),
            [
                'customer' => [
                    'name' => 'Test Customer',
                    'phone' => '081234567890',
                    'email' => 'test-weight@example.com',
                ],

                'shipping_data' => [
                    'address' => 'Jl. Test No. 1',
                    'district' => 'Kertak Hanyar',
                    'city' => 'Banjar',
                    'province' => 'Kalimantan Selatan',
                    'postal_code' => '70654',
                    'method' => 'Kurir',
                ],

                'transaction_date' => now('Asia/Makassar')
                    ->format('Y-m-d\TH:i'),

                'payment_method' => 'Transfer Bank',

                'discount' => 0,
                'shipping' => 0,

                'items' => [
                    [
                        'product_variant_id' => $variant->id,
                        'qty' => 1,
                        'custom_name' => 'TEST',
                        'custom_number' => '10',
                    ],
                ],
            ]);

        $this->assertSame(
            201,
            $response->status(),
            sprintf(
                'Unexpected response: class=%s status=%s location=%s content=%s',
                get_class($response),
                $response->status(),
                $response->headers->get('Location') ?? 'null',
                $response->getContent()
            )
        );

        $this->assertTrue(
            $response->json('success') === true,
            'Response JSON: ' . $response->getContent()
        );

        $transactionId = $response->json('data.id');

        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $transactionId,
            'product_variant_id' => $variant->id,
            'weight' => 750,
        ]);
    }

    public function test_pending_transaction_cannot_be_deleted(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'PENDING',
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('admin.transactions.destroy', $transaction));

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'PENDING',
        ]);
    }

    public function test_paid_transaction_cannot_be_deleted(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'PAID',
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('admin.transactions.destroy', $transaction));

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'PAID',
        ]);
    }

    public function test_completed_transaction_cannot_be_deleted(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'COMPLETED',
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('admin.transactions.destroy', $transaction));

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'COMPLETED',
        ]);
    }

    public function test_paid_transaction_cannot_be_checked_against_doku(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'PAID',
            'va_number' => '190089123456789012',
            'invoice_number' => 'INV-TEST-PAID',
        ]);

        $this->mock(DokuService::class, function ($mock) {
            $mock->shouldNotReceive('checkVirtualAccountStatus');
        });

        $response = $this
            ->actingAs($user)
            ->postJson(route('admin.transactions.check-payment', $transaction));

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Transaksi sudah diproses.',
            ]);
    }

    public function test_cancelled_transaction_without_stock_movement_can_be_deleted(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'CANCELLED',
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('admin.transactions.destroy', $transaction));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }

    public function test_transaction_with_stock_movement_cannot_be_deleted(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'CANCELLED',
        ]);

        StockMovement::factory()->create([
            'transaction_id' => $transaction->id,
            'type' => 'IN',
            'qty' => 1,
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('admin.transactions.destroy', $transaction));

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Transaksi yang memiliki riwayat stok tidak dapat dihapus.',
            ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'CANCELLED',
        ]);
    }

    public function test_pending_transaction_can_be_cancelled(): void
    {
        $user = $this->superAdmin();

        $transaction = $this->createTestTransaction();

        $response = $this
            ->actingAs($user)
            ->patchJson(route('admin.transactions.cancel', $transaction));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'CANCELLED',
                ],
            ]);

        $transaction->refresh();

        $this->assertSame('CANCELLED', $transaction->status);

        $this->assertDatabaseMissing('stock_movements', [
            'transaction_id' => $transaction->id,
        ]);
    }

    public function test_paid_transaction_can_be_cancelled_and_stock_is_restored(): void
    {
        $user = $this->superAdmin();

        $transaction = $this->createTestTransaction();

        $inventory = Inventory::query()
            ->where(
                'product_variant_id',
                $transaction->items->first()->product_variant_id
            )
            ->firstOrFail();

        $stockBefore = (int) $inventory->stock;
        $quantity = (int) $transaction->items->sum('qty');

        $service = app(\App\Services\InventoryStockService::class);

        $service->decreaseForTransaction(
            $transaction,
            'Controller cancellation test - deduction'
        );

        $this->assertSame(
            $stockBefore - $quantity,
            (int) $inventory->refresh()->stock
        );

        $response = $this
            ->actingAs($user)
            ->patchJson(route('admin.transactions.cancel', $transaction));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'CANCELLED',
                ],
            ]);

        $transaction->refresh();
        $inventory->refresh();

        $this->assertSame('CANCELLED', $transaction->status);
        $this->assertSame($stockBefore, (int) $inventory->stock);

        $this->assertDatabaseHas('stock_movements', [
            'transaction_id' => $transaction->id,
            'type' => 'IN',
            'qty' => $quantity,
        ]);
    }

    public function test_cancelled_transaction_cannot_be_cancelled_again(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'CANCELLED',
        ]);

        $response = $this
            ->actingAs($user)
            ->patchJson(route('admin.transactions.cancel', $transaction));

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Transaksi sudah dibatalkan sebelumnya.',
            ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'CANCELLED',
        ]);
    }

    public function test_expired_transaction_cannot_be_cancelled(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'EXPIRED',
            'va_expired_at' => now('UTC')->subMinute(),
        ]);

        $response = $this
            ->actingAs($user)
            ->patchJson(route('admin.transactions.cancel', $transaction));

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'EXPIRED',
        ]);
    }

    public function test_cancelled_transaction_cannot_be_checked_against_doku(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'CANCELLED',
            'va_number' => '190089123456789012',
            'invoice_number' => 'INV-TEST-CANCELLED',
        ]);

        $this->mock(DokuService::class, function ($mock) {
            $mock->shouldNotReceive('checkVirtualAccountStatus');
        });

        $response = $this
            ->actingAs($user)
            ->postJson(route('admin.transactions.check-payment', $transaction));

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Transaksi sudah diproses.',
            ]);
    }

    public function test_expired_transaction_cannot_be_checked_against_doku(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'EXPIRED',
            'va_number' => '190089123456789012',
            'invoice_number' => 'INV-TEST-EXPIRED',
        ]);

        $this->mock(DokuService::class, function ($mock) {
            $mock->shouldNotReceive('checkVirtualAccountStatus');
        });

        $response = $this
            ->actingAs($user)
            ->postJson(route('admin.transactions.check-payment', $transaction));

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Transaksi sudah diproses.',
            ]);
    }

    public function test_completed_transaction_cannot_be_checked_against_doku(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'COMPLETED',
            'va_number' => '190089123456789012',
            'invoice_number' => 'INV-TEST-COMPLETED',
        ]);

        $this->mock(DokuService::class, function ($mock) {
            $mock->shouldNotReceive('checkVirtualAccountStatus');
        });

        $response = $this
            ->actingAs($user)
            ->postJson(route('admin.transactions.check-payment', $transaction));

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Transaksi sudah diproses.',
            ]);
    }

    public function test_cancelled_transaction_creates_order_cancelled_history(): void
    {
        $user = $this->superAdmin();

        $transaction = $this->createTestTransaction();

        $response = $this
            ->actingAs($user)
            ->patchJson(
                route('admin.transactions.cancel', $transaction)
            );

        $response->assertOk();

        $this->assertDatabaseHas('order_status_histories', [
            'transaction_id' => $transaction->id,
            'status' => Transaction::ORDER_CANCELLED,
        ]);
    }

    public function test_admin_can_view_transaction_detail(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'PAID',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.transactions.show', $transaction));

        $response
            ->assertOk()
            ->assertViewIs('admin.transactions.show')
            ->assertViewHas('transaction');
    }

    public function test_admin_can_print_transaction_invoice()
    {
        $user = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $transaction = Transaction::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.transactions.print', $transaction->invoice_number));

        $response
            ->assertOk()
            ->assertViewIs('admin.transactions.print')
            ->assertViewHas('transaction');
    }
}