<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;

class BackfillOrderStatusHistory extends Command
{
    protected $signature = 'orders:backfill-status-history';

    protected $description = 'Create initial order status history for old transactions';

    public function handle(): int
    {
        Transaction::chunkById(100, function ($transactions) {

            foreach ($transactions as $transaction) {

                if ($transaction->orderStatusHistories()->exists()) {
                    continue;
                }

                $status = match ($transaction->status) {
                    'PAID' => 'PAYMENT_CONFIRMED',
                    'COMPLETED' => 'ORDER_COMPLETED',
                    'CANCELLED' => 'ORDER_CANCELLED',
                    'EXPIRED' => 'ORDER_EXPIRED',
                    default => 'ORDER_CREATED',
                };

                $note = match ($status) {
                    'PAYMENT_CONFIRMED' =>
                        'Pembayaran berhasil dikonfirmasi.',

                    'ORDER_COMPLETED' =>
                        'Pesanan telah selesai.',

                    'ORDER_CANCELLED' =>
                        'Pesanan dibatalkan.',

                    'ORDER_EXPIRED' =>
                        'Pesanan kadaluarsa.',

                    default =>
                        'Pesanan dibuat.',
                };

                $transaction->orderStatusHistories()->create([
                    'status' => $status,
                    'note' => $note,
                ]);
            }

        });

        $this->info('Order status history berhasil dibuat.');

        return self::SUCCESS;
    }
}