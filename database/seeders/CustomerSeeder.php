<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Michael Brown',
                'phone' => '081234567890',
                'email' => 'michael@example.com',
            ],
            [
                'name' => 'Emily Davis',
                'phone' => '081234567891',
                'email' => 'emily@example.com',
            ],
            [
                'name' => 'James Anderson',
                'phone' => '081234567892',
                'email' => 'james@example.com',
            ],
            [
                'name' => 'Sarah Wilson',
                'phone' => '081234567893',
                'email' => 'sarah@example.com',
            ],
            [
                'name' => 'David Miller',
                'phone' => '081234567894',
                'email' => 'david@example.com',
            ],
            [
                'name' => 'Olivia Taylor',
                'phone' => '081234567895',
                'email' => 'olivia@example.com',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(
                [
                    'email' => $customer['email'],
                ],
                [
                    'name' => $customer['name'],
                    'phone' => $customer['phone'],
                ]
            );
        }
    }
}