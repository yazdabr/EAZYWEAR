<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\InventoryStockService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InventoryStockServiceTest extends TestCase
{
    use DatabaseTransactions;

    private function createTestTransaction(int $quantity = 2): Transaction
    {
        $inventory = Inventory::query()
            ->where('stock', '>=', $quantity)
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

    public function test_stock_is_deducted_for_paid_transaction(): void
    {
        $transaction = $this->createTestTransaction();

        $inventory = Inventory::where(
            'product_variant_id',
            $transaction->items->first()->product_variant_id
        )->firstOrFail();

        $stockBefore = (int) $inventory->stock;
        $quantity = (int) $transaction->items->sum('qty');

        $service = app(InventoryStockService::class);

        $service->decreaseForTransaction(
            $transaction,
            'Automated local test'
        );

        $inventory->refresh();
        $transaction->refresh();
        $transaction->load('items');

        $this->assertSame(
            $stockBefore - $quantity,
            (int) $inventory->stock
        );

        $this->assertSame('PAID', $transaction->status);
        $this->assertNotNull($transaction->paid_at);

        foreach ($transaction->items as $item) {
            $this->assertNotNull($item->stock_deducted_at);

            $movementExists = StockMovement::query()
                ->where('transaction_id', $transaction->id)
                ->where('transaction_item_id', $item->id)
                ->where('type', 'OUT')
                ->exists();

            $this->assertTrue($movementExists);
        }
    }

    public function test_stock_is_not_deducted_twice_for_same_transaction(): void
    {
        $transaction = $this->createTestTransaction();

        $inventory = Inventory::where(
            'product_variant_id',
            $transaction->items->first()->product_variant_id
        )->firstOrFail();

        $service = app(InventoryStockService::class);

        $service->decreaseForTransaction(
            $transaction,
            'Idempotency test - first call'
        );

        $inventory->refresh();

        $stockAfterFirstCall = (int) $inventory->stock;

        $movementCountAfterFirstCall = StockMovement::query()
            ->where('transaction_id', $transaction->id)
            ->where('type', 'OUT')
            ->count();

        $transaction->refresh();

        $service->decreaseForTransaction(
            $transaction,
            'Idempotency test - second call'
        );

        $inventory->refresh();

        $stockAfterSecondCall = (int) $inventory->stock;

        $movementCountAfterSecondCall = StockMovement::query()
            ->where('transaction_id', $transaction->id)
            ->where('type', 'OUT')
            ->count();

        $this->assertSame(
            $stockAfterFirstCall,
            $stockAfterSecondCall
        );

        $this->assertSame(
            $movementCountAfterFirstCall,
            $movementCountAfterSecondCall
        );
    }

    public function test_stock_is_restored_for_paid_transaction(): void
    {
        $transaction = $this->createTestTransaction();

        $inventory = Inventory::where(
            'product_variant_id',
            $transaction->items->first()->product_variant_id
        )->firstOrFail();

        $stockBefore = (int) $inventory->stock;
        $quantity = (int) $transaction->items->sum('qty');

        $service = app(InventoryStockService::class);

        $service->decreaseForTransaction(
            $transaction,
            'Restore test - deduction'
        );

        $inventory->refresh();

        $this->assertSame(
            $stockBefore - $quantity,
            (int) $inventory->stock
        );

        $transaction->refresh();

        $service->restoreForTransaction(
            $transaction,
            'Restore test - restoration'
        );

        $inventory->refresh();
        $transaction->refresh();
        $transaction->load('items');

        $this->assertSame(
            $stockBefore,
            (int) $inventory->stock
        );

        $this->assertSame('CANCELLED', $transaction->status);

        foreach ($transaction->items as $item) {
            $this->assertNotNull($item->stock_deducted_at);
            $this->assertNotNull($item->stock_restored_at);

            $movementExists = StockMovement::query()
                ->where('transaction_id', $transaction->id)
                ->where('transaction_item_id', $item->id)
                ->where('type', 'IN')
                ->exists();

            $this->assertTrue($movementExists);
        }
    }

    public function test_stock_is_not_restored_twice_for_same_transaction(): void
    {
        $transaction = $this->createTestTransaction();

        $inventory = Inventory::where(
            'product_variant_id',
            $transaction->items->first()->product_variant_id
        )->firstOrFail();

        $service = app(InventoryStockService::class);

        $service->decreaseForTransaction(
            $transaction,
            'Duplicate restore test - deduction'
        );

        $transaction->refresh();

        $service->restoreForTransaction(
            $transaction,
            'Duplicate restore test - first restoration'
        );

        $inventory->refresh();

        $stockAfterFirstRestore = (int) $inventory->stock;

        $movementCountAfterFirstRestore = StockMovement::query()
            ->where('transaction_id', $transaction->id)
            ->where('type', 'IN')
            ->count();

        $transaction->refresh();

        $service->restoreForTransaction(
            $transaction,
            'Duplicate restore test - second restoration'
        );

        $inventory->refresh();

        $stockAfterSecondRestore = (int) $inventory->stock;

        $movementCountAfterSecondRestore = StockMovement::query()
            ->where('transaction_id', $transaction->id)
            ->where('type', 'IN')
            ->count();

        $this->assertSame(
            $stockAfterFirstRestore,
            $stockAfterSecondRestore
        );

        $this->assertSame(
            $movementCountAfterFirstRestore,
            $movementCountAfterSecondRestore
        );

        $this->assertSame(
            'CANCELLED',
            $transaction->refresh()->status
        );
    }

    public function test_stock_restoration_creates_in_movement_with_transaction_references(): void
    {
        $transaction = $this->createTestTransaction();

        $service = app(InventoryStockService::class);

        $service->decreaseForTransaction(
            $transaction,
            'Movement reference test - deduction'
        );

        $transaction->refresh();

        $service->restoreForTransaction(
            $transaction,
            'Movement reference test - restoration'
        );

        foreach ($transaction->items as $item) {
            $movement = StockMovement::query()
                ->where('transaction_id', $transaction->id)
                ->where('transaction_item_id', $item->id)
                ->where('type', 'IN')
                ->first();

            $this->assertNotNull($movement);
            $this->assertSame($transaction->id, $movement->transaction_id);
            $this->assertSame($item->id, $movement->transaction_item_id);
            $this->assertSame((int) $item->qty, (int) $movement->qty);
        }
    }

    public function test_restoring_transaction_changes_status_to_cancelled(): void
    {
        $transaction = $this->createTestTransaction();

        $service = app(InventoryStockService::class);

        $service->decreaseForTransaction(
            $transaction,
            'Status restore test - deduction'
        );

        $this->assertSame('PAID', $transaction->refresh()->status);

        $service->restoreForTransaction(
            $transaction,
            'Status restore test - restoration'
        );

        $this->assertSame(
            'CANCELLED',
            $transaction->refresh()->status
        );
    }
}