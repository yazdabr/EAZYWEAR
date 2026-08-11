<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Jersey Sepak Bola',
                'description' => 'Jersey sepak bola custom dengan bahan premium dan nyaman digunakan.',
                'image' => 'images/categories/jersey-sepak-bola.jpg',
                'status' => true,
            ],
            [
                'name' => 'Jersey Futsal',
                'description' => 'Jersey futsal dengan desain modern dan bahan ringan.',
                'image' => 'images/categories/jersey-futsal.jpg',
                'status' => true,
            ],
            [
                'name' => 'Jersey Basket',
                'description' => 'Jersey basket custom dengan bahan breathable.',
                'image' => 'images/categories/jersey-basket.jpg',
                'status' => true,
            ],
            [
                'name' => 'Jersey Esports',
                'description' => 'Jersey esports dengan desain custom untuk kebutuhan tim.',
                'image' => 'images/categories/jersey-esports.jpg',
                'status' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                [
                    'name' => $category['name'],
                ],
                [
                    'slug' => Str::slug($category['name']),
                    'description' => $category['description'],
                    'image' => $category['image'],
                    'status' => $category['status'],
                ]
            );
        }
    }
}