<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $variants = ProductVariant::all();

        foreach ($variants as $variant) {
            Inventory::updateOrCreate(
                [
                    'product_variant_id' => $variant->id,
                ],
                [
                    'stock' => rand(25, 150),
                ]
            );
        }
    }
}