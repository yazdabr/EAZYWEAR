<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\DokuService;
use App\Models\FulfillmentHold;
use App\Models\FulfillmentSlot;
use App\Http\Controllers\Admin\TransactionController;
use App\Mail\OrderShippedMail;
use App\Models\TransactionNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{

    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        FulfillmentHold::query()->delete();

        FulfillmentSlot::query()
            ->whereBetween('date', [
                '2026-10-30',
                '2026-11-03',
            ])
            ->update([
                'used_count' => 0,
                'capacity' => 100,
            ]);
    }
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

    public function test_cancelled_transaction_with_released_fulfillment_hold_can_be_deleted(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'CANCELLED',
            'fulfillment_date' => '2026-10-30',
        ]);

        $slot = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->firstOrFail();

        $hold = FulfillmentHold::query()->create([
            'transaction_id' => $transaction->id,
            'fulfillment_slot_id' => $slot->id,
            'status' => FulfillmentHold::RELEASED,
            'released_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('admin.transactions.destroy', $transaction));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('fulfillment_holds', [
            'id' => $hold->id,
        ]);

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }

    public function test_cancelled_transaction_with_active_fulfillment_hold_cannot_be_deleted(): void
    {
        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'CANCELLED',
            'fulfillment_date' => '2026-10-30',
        ]);

        $slot = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->firstOrFail();

        $hold = FulfillmentHold::query()->create([
            'transaction_id' => $transaction->id,
            'fulfillment_slot_id' => $slot->id,
            'status' => FulfillmentHold::HELD,
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('admin.transactions.destroy', $transaction));

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Fulfillment hold transaksi belum dilepas dan transaksi tidak dapat dihapus.',
            ]);

        $this->assertDatabaseHas('fulfillment_holds', [
            'id' => $hold->id,
            'transaction_id' => $transaction->id,
            'status' => FulfillmentHold::HELD,
        ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'CANCELLED',
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

    public function test_admin_can_complete_pickup_order(): void
    {
        $transaction = Transaction::factory()->create([
            'status' => 'ORDER_PROCESSING',
            'shipping_method' => 'Ambil di Tempat',
        ]);

        $response = app(TransactionController::class)
            ->complete($transaction);

        $this->assertSame(200, $response->status());

        $this->assertSame([
            'success' => true,
            'message' => 'Pesanan berhasil diselesaikan.',
            'data' => [
                'id' => $transaction->id,
                'status' => 'ORDER_COMPLETED',
            ],
        ], $response->getData(true));

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'ORDER_COMPLETED',
        ]);

        $this->assertDatabaseHas('order_status_histories', [
            'transaction_id' => $transaction->id,
            'status' => Transaction::ORDER_COMPLETED,
        ]);
    }

    public function test_admin_can_complete_courier_order(): void
    {
        $transaction = Transaction::factory()->create([
            'status' => 'ORDER_SHIPPED',
            'shipping_method' => 'Kurir',
        ]);

        $response = app(TransactionController::class)
            ->complete($transaction);

        $this->assertSame(200, $response->status());

        $this->assertSame([
            'success' => true,
            'message' => 'Pesanan berhasil diselesaikan.',
            'data' => [
                'id' => $transaction->id,
                'status' => 'ORDER_COMPLETED',
            ],
        ], $response->getData(true));

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'ORDER_COMPLETED',
        ]);

        $this->assertDatabaseHas('order_status_histories', [
            'transaction_id' => $transaction->id,
            'status' => Transaction::ORDER_COMPLETED,
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

    public function test_admin_shipping_sends_only_one_shipping_email(): void
    {
        Mail::fake();

        $user = $this->superAdmin();

        $transaction = Transaction::factory()->create([
            'status' => 'ORDER_PROCESSING',
            'shipping_method' => 'Kurir',
            'shipping_email' => 'customer@example.com',
            'courier_code' => null,
            'courier_service_code' => null,
            'biteship_order_id' => null,
            'biteship_tracking_id' => null,
            'biteship_waybill_id' => null,
            'biteship_status' => null,
            'courier' => null,
            'tracking_number' => null,
        ]);

        $requestData = [
            'courier' => 'JNE',
            'tracking_number' => 'JNE123456789',
        ];

        // Pastikan fixture dan payload test memang sesuai dengan
        // branch manual shipping yang sedang diaudit.
        $this->assertNull($transaction->courier_code);
        $this->assertNull($transaction->courier_service_code);
        $this->assertSame('JNE', $requestData['courier']);
        $this->assertSame('JNE123456789', $requestData['tracking_number']);

        $response = $this
            ->actingAs($user)
            ->patchJson(
                route('admin.transactions.ship', $transaction),
                $requestData
            );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $transaction->id,
                    'status' => 'ORDER_SHIPPED',
                    'courier' => 'JNE',
                    'tracking_number' => 'JNE123456789',
                ],
            ]);

        Mail::assertSent(
            OrderShippedMail::class,
            1
        );

        Mail::assertSent(
            OrderShippedMail::class,
            function (OrderShippedMail $mail) use ($transaction) {
                return $mail->transaction->id === $transaction->id;
            }
        );

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'ORDER_SHIPPED',
            'courier' => 'JNE',
            'tracking_number' => 'JNE123456789',
        ]);

        $this->assertDatabaseHas('transaction_notifications', [
            'transaction_id' => $transaction->id,
            'type' => 'ORDER_SHIPPED_EMAIL',
        ]);

        $this->assertSame(
            1,
            TransactionNotification::query()
                ->where('transaction_id', $transaction->id)
                ->where('type', 'ORDER_SHIPPED_EMAIL')
                ->count()
        );

        $this->assertNotNull(
            TransactionNotification::query()
                ->where('transaction_id', $transaction->id)
                ->where('type', 'ORDER_SHIPPED_EMAIL')
                ->value('sent_at')
        );

        /*
        * Second request:
        * transaksi sudah ORDER_SHIPPED, sehingga tidak boleh
        * mengirim email kedua atau membuat notification kedua.
        */
        $transaction->refresh();

        $secondResponse = $this
            ->actingAs($user)
            ->patchJson(
                route('admin.transactions.ship', $transaction),
                $requestData
            );

        $secondResponse
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
        ]);

        Mail::assertSent(
            OrderShippedMail::class,
            1
        );

        $this->assertSame(
            1,
            TransactionNotification::query()
                ->where('transaction_id', $transaction->id)
                ->where('type', 'ORDER_SHIPPED_EMAIL')
                ->count()
        );

        $this->assertDatabaseHas('transaction_notifications', [
            'transaction_id' => $transaction->id,
            'type' => 'ORDER_SHIPPED_EMAIL',
        ]);
    }
    public function test_pending_special_batch_transaction_cancellation_releases_fulfillment_hold(): void
    {
        $user = $this->superAdmin();

        $transaction = $this->createTestTransaction();

        $transaction->update([
            'status' => 'PENDING',
            'fulfillment_date' => '2026-10-30',
        ]);

        $slot = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->firstOrFail();

        $hold = FulfillmentHold::query()->create([
            'transaction_id' => $transaction->id,
            'fulfillment_slot_id' => $slot->id,
            'status' => FulfillmentHold::HELD,
        ]);

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
        $hold->refresh();
        $slot->refresh();

        $this->assertSame('CANCELLED', $transaction->status);
        $this->assertSame(
            FulfillmentHold::RELEASED,
            $hold->status
        );
        $this->assertNotNull($hold->released_at);
        $this->assertSame(0, (int) $slot->used_count);
    }
    public function test_paid_special_batch_transaction_cancellation_releases_allocation_and_capacity(): void
    {
        $user = $this->superAdmin();

        $transaction = $this->createTestTransaction();

        $transaction->update([
            'status' => 'PAID',
            'fulfillment_date' => '2026-10-30',
        ]);

        $slot = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->firstOrFail();

        $hold = FulfillmentHold::query()->create([
            'transaction_id' => $transaction->id,
            'fulfillment_slot_id' => $slot->id,
            'status' => FulfillmentHold::CONVERTED,
        ]);

        $slot->update([
            'used_count' => 1,
        ]);

        $inventory = Inventory::query()
            ->where(
                'product_variant_id',
                $transaction->items->first()->product_variant_id
            )
            ->firstOrFail();

        $stockBeforeCancellation = (int) $inventory->stock;

        $service = app(\App\Services\InventoryStockService::class);

        $service->decreaseForTransaction(
            $transaction,
            'Controller cancellation test - fulfillment allocation'
        );

        $stockAfterPayment = (int) $inventory->refresh()->stock;

        $this->assertSame(
            $stockBeforeCancellation - $transaction->items->sum('qty'),
            $stockAfterPayment
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
        $hold->refresh();
        $slot->refresh();
        $inventory->refresh();

        $this->assertSame('CANCELLED', $transaction->status);
        $this->assertSame(
            FulfillmentHold::RELEASED,
            $hold->status
        );
        $this->assertNotNull($hold->released_at);
        $this->assertSame(0, (int) $slot->used_count);
        $this->assertSame($stockBeforeCancellation, (int) $inventory->stock);
    }
}