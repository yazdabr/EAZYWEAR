<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            [
                'name' => 'Hitam',
                'hex_code' => '#000000',
            ],
            [
                'name' => 'Putih',
                'hex_code' => '#FFFFFF',
            ],
            [
                'name' => 'Merah',
                'hex_code' => '#EF4444',
            ],
            [
                'name' => 'Biru',
                'hex_code' => '#2563EB',
            ],
        ];

        foreach ($colors as $color) {
            Color::updateOrCreate(
                [
                    'name' => $color['name'],
                ],
                [
                    'hex_code' => $color['hex_code'],
                ]
            );
        }
    }
}