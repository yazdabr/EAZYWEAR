<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Master Data
        |--------------------------------------------------------------------------
        */

        $this->call([
            CategorySeeder::class,
            SizeSeeder::class,
            ColorSeeder::class,
            ProductSeeder::class,
            ProductVariantSeeder::class,
            ProductImageSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Inventory
        |--------------------------------------------------------------------------
        */

        $this->call([
            InventorySeeder::class,
            StockMovementSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Customer & Transaction
        |--------------------------------------------------------------------------
        */

        $this->call([
            CustomerSeeder::class,
            TransactionSeeder::class,
            TransactionItemSeeder::class,
        ]);
    }
}