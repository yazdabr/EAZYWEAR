<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Services\InventoryStockService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InventoryStockServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_stock_is_deducted_for_paid_transaction(): void
    {
        $transaction = Transaction::with('items')
            ->findOrFail(54);

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
        $transaction = Transaction::with('items')->findOrFail(54);

        $inventory = Inventory::where(
            'product_variant_id',
            $transaction->items->first()->product_variant_id
        )->firstOrFail();

        $service = app(InventoryStockService::class);

        // Pemanggilan pertama
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

        // Pemanggilan kedua
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
}