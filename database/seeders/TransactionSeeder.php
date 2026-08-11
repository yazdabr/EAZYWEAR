<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();

        $transactions = [
            [
                'invoice_number' => 'INV-20260807-001',
                'customer_index' => 0,
                'date' => '2026-08-07 09:15:00',
                'payment_method' => 'Transfer',
                'subtotal' => 447000,
                'discount' => 0,
                'shipping' => 20000,
                'status' => 'COMPLETED',
                'source' => 'API',
            ],
            [
                'invoice_number' => 'INV-20260807-002',
                'customer_index' => 1,
                'date' => '2026-08-07 10:30:00',
                'payment_method' => 'EDC',
                'subtotal' => 398000,
                'discount' => 10000,
                'shipping' => 20000,
                'status' => 'PAID',
                'source' => 'Smart EDC',
            ],
            [
                'invoice_number' => 'INV-20260807-003',
                'customer_index' => 2,
                'date' => '2026-08-07 13:45:00',
                'payment_method' => 'Transfer',
                'subtotal' => 497000,
                'discount' => 0,
                'shipping' => 20000,
                'status' => 'COMPLETED',
                'source' => 'API',
            ],
            [
                'invoice_number' => 'INV-20260807-004',
                'customer_index' => 3,
                'date' => '2026-08-07 15:20:00',
                'payment_method' => 'EDC',
                'subtotal' => 338000,
                'discount' => 0,
                'shipping' => 20000,
                'status' => 'PAID',
                'source' => 'Smart EDC',
            ],
            [
                'invoice_number' => 'INV-20260806-005',
                'customer_index' => 4,
                'date' => '2026-08-06 11:10:00',
                'payment_method' => 'QRIS',
                'subtotal' => 298000,
                'discount' => 15000,
                'shipping' => 20000,
                'status' => 'COMPLETED',
                'source' => 'Android POS',
            ],
            [
                'invoice_number' => 'INV-20260806-006',
                'customer_index' => 5,
                'date' => '2026-08-06 16:40:00',
                'payment_method' => 'Cash',
                'subtotal' => 159000,
                'discount' => 0,
                'shipping' => 0,
                'status' => 'PENDING',
                'source' => 'Android POS',
            ],
        ];

        foreach ($transactions as $data) {
            $customer = $customers[$data['customer_index']];

            $total =
                $data['subtotal']
                - $data['discount']
                + $data['shipping'];

            Transaction::updateOrCreate(
                [
                    'invoice_number' => $data['invoice_number'],
                ],
                [
                    'customer_id' => $customer->id,
                    'transaction_date' => Carbon::parse($data['date']),
                    'payment_method' => $data['payment_method'],
                    'subtotal' => $data['subtotal'],
                    'discount' => $data['discount'],
                    'shipping' => $data['shipping'],
                    'total' => $total,
                    'status' => $data['status'],
                    'source' => $data['source'],
                ]
            );
        }
    }
}