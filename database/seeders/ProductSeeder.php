<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'product_code' => 'PRD-001',
                'category' => 'Jersey Sepak Bola',
                'name' => 'Apex Pro Kit',
                'description' => 'Jersey kustom premium terbuat dari kain dry-fit yang bernapas dengan desain modern.',
                'material' => 'Dry-Fit Premium',
                'status' => true,
            ],
            [
                'product_code' => 'PRD-002',
                'category' => 'Jersey Sepak Bola',
                'name' => 'Elite Football Jersey',
                'description' => 'Jersey sepak bola premium dengan bahan ringan dan nyaman.',
                'material' => 'Polyester Dry-Fit',
                'status' => true,
            ],
            [
                'product_code' => 'PRD-003',
                'category' => 'Jersey Futsal',
                'name' => 'Velocity Futsal Jersey',
                'description' => 'Jersey futsal dengan desain sporty dan bahan breathable.',
                'material' => 'Dry-Fit',
                'status' => true,
            ],
            [
                'product_code' => 'PRD-004',
                'category' => 'Jersey Basket',
                'name' => 'Hoops Elite Jersey',
                'description' => 'Jersey basket custom dengan desain modern.',
                'material' => 'Mesh Premium',
                'status' => true,
            ],
            [
                'product_code' => 'PRD-005',
                'category' => 'Jersey Esports',
                'name' => 'Cyber Team Jersey',
                'description' => 'Jersey esports custom untuk kebutuhan tim profesional.',
                'material' => 'Dry-Fit Premium',
                'status' => true,
            ],
            [
                'product_code' => 'PRD-006',
                'category' => 'Jersey Sepak Bola',
                'name' => 'Classic Football Kit',
                'description' => 'Jersey sepak bola dengan gaya klasik dan nyaman digunakan.',
                'material' => 'Polyester',
                'status' => true,
            ],
            [
                'product_code' => 'PRD-007',
                'category' => 'Jersey Futsal',
                'name' => 'Speed Futsal Kit',
                'description' => 'Jersey futsal ringan untuk menunjang aktivitas pertandingan.',
                'material' => 'Dry-Fit',
                'status' => true,
            ],
            [
                'product_code' => 'PRD-008',
                'category' => 'Jersey Basket',
                'name' => 'Street Basketball Jersey',
                'description' => 'Jersey basket dengan desain street style modern.',
                'material' => 'Mesh',
                'status' => false,
            ],
        ];

        foreach ($products as $data) {
            $category = Category::where('name', $data['category'])->firstOrFail();

            Product::updateOrCreate(
                [
                    'product_code' => $data['product_code'],
                ],
                [
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'description' => $data['description'],
                    'material' => $data['material'],
                    'status' => $data['status'],
                ]
            );
        }
    }
}