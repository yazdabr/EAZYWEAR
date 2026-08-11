<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;

class StockMovementSeeder extends Seeder
{
    public function run(): void
    {
        $inventories = Inventory::all();

        foreach ($inventories as $inventory) {
            StockMovement::updateOrCreate(
                [
                    'inventory_id' => $inventory->id,
                    'type' => 'IN',
                    'description' => 'Stok awal produk',
                ],
                [
                    'qty' => $inventory->stock,
                    'stock_before' => 0,
                    'stock_after' => $inventory->stock,
                ]
            );
        }
    }
}