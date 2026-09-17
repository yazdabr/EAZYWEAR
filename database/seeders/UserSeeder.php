<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'name' => 'EazyProduksi',
            ],
            [
                'email' => 'eazyproduksi@eazywear.id',
                'password' => 'produksi123',
                'role' => 'production',
            ]
        );
    }
}