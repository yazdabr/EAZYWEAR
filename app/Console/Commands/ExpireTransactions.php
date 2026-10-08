<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\TransactionExpiryService;
use Illuminate\Console\Command;

class ExpireTransactions extends Command
{
    protected $signature = 'transactions:expire';

    protected $description = 'Mengubah transaksi PENDING yang sudah melewati batas pembayaran menjadi EXPIRED';

    public function handle(TransactionExpiryService $expiryService): int
    {
        $expiredCount = 0;

        Transaction::query()
            ->where('status', 'PENDING')
            ->where(function ($query) {
                $query
                    ->where(function ($query) {
                        $query
                            ->where('payment_method', 'VA')
                            ->whereNotNull('va_expired_at')
                            ->where('va_expired_at', '<=', now());
                    })
                    ->orWhere(function ($query) {
                        $query
                            ->where('payment_method', 'QRIS')
                            ->whereNotNull('qris_expired_at')
                            ->where('qris_expired_at', '<=', now());
                    });
            })
            ->orderBy('id')
            ->each(function (Transaction $transaction) use (
                $expiryService,
                &$expiredCount
            ) {
                if ($expiryService->expire($transaction)) {
                    $expiredCount++;

                    $this->info(
                        "Transaction #{$transaction->id} ({$transaction->invoice_number}) → EXPIRED"
                    );
                }
            });

        $this->info("Total transaksi expired: {$expiredCount}");

        return self::SUCCESS;
    }
}
