<?php

namespace Database\Factories;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockMovement>
 */
class StockMovementFactory extends Factory
{
    public function definition(): array
    {
        $inventoryId = Inventory::query()->value('id');

        if (!$inventoryId) {
            throw new \RuntimeException(
                'Tidak ada data inventory di database testing.'
            );
        }

        return [
            'inventory_id' => $inventoryId,
            'transaction_id' => null,
            'transaction_item_id' => null,
            'type' => 'IN',
            'qty' => 1,
            'stock_before' => 0,
            'stock_after' => 1,
            'description' => 'Test stock movement',
        ];
    }
}