<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::orderBy('id')->get();

        foreach ($products as $index => $product) {
            ProductImage::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'sort_order' => 1,
                ],
                [
                    'image' => 'images/products/' . ($index + 1) . '.png',
                    'is_thumbnail' => true,
                ]
            );
        }
    }
}