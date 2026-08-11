<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $sizes = Size::all();
        $colors = Color::all();

        foreach ($products as $product) {
            foreach ($sizes as $size) {

                $color = $colors->random();

                ProductVariant::updateOrCreate(
                    [
                        'sku' => $product->product_code . '-' . $size->name,
                    ],
                    [
                        'product_id' => $product->id,
                        'size_id' => $size->id,
                        'color_id' => $color->id,
                        'price' => $this->getPrice($product->product_code),
                    ]
                );
            }
        }
    }

    private function getPrice(string $productCode): float
    {
        return match ($productCode) {
            'PRD-001' => 149000,
            'PRD-002' => 199000,
            'PRD-003' => 139000,
            'PRD-004' => 159000,
            'PRD-005' => 179000,
            'PRD-006' => 129000,
            'PRD-007' => 139000,
            'PRD-008' => 169000,
            default => 149000,
        };
    }
}