<?php

namespace Database\Seeders;

use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Seeder;

class TransactionItemSeeder extends Seeder
{
    public function run(): void
    {
        $transactions = Transaction::orderBy('id')->get();

        $variants = ProductVariant::with([
            'product',
            'size',
        ])->get();

        if ($transactions->isEmpty() || $variants->isEmpty()) {
            return;
        }

        $items = [
            [
                'transaction_index' => 0,
                'variant_index' => 0,
                'qty' => 2,
            ],
            [
                'transaction_index' => 0,
                'variant_index' => 5,
                'qty' => 1,
            ],

            [
                'transaction_index' => 1,
                'variant_index' => 10,
                'qty' => 2,
            ],

            [
                'transaction_index' => 2,
                'variant_index' => 15,
                'qty' => 2,
            ],
            [
                'transaction_index' => 2,
                'variant_index' => 20,
                'qty' => 1,
            ],

            [
                'transaction_index' => 3,
                'variant_index' => 25,
                'qty' => 2,
            ],

            [
                'transaction_index' => 4,
                'variant_index' => 30,
                'qty' => 2,
            ],

            [
                'transaction_index' => 5,
                'variant_index' => 35,
                'qty' => 1,
            ],
        ];

        foreach ($items as $item) {
            $transaction = $transactions[$item['transaction_index']] ?? null;
            $variant = $variants[$item['variant_index']] ?? null;

            if (!$transaction || !$variant) {
                continue;
            }

            $qty = $item['qty'];
            $price = $variant->price;
            $subtotal = $price * $qty;

            TransactionItem::updateOrCreate(
                [
                    'transaction_id' => $transaction->id,
                    'product_variant_id' => $variant->id,
                ],
                [
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]
            );
        }
    }
}