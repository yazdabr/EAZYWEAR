<?php

namespace App\Services;

use App\Mail\OrderCompletedMail;
use App\Models\Transaction;
use App\Models\TransactionNotification;
use App\Services\EmailArchiveService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionCompletionService
{
    public function completeManually(Transaction $transaction): Transaction
    {
        return $this->complete($transaction, false);
    }

    public function completeFromBiteship(Transaction $transaction): Transaction
    {
        return $this->complete($transaction, true);
    }

    private function complete(
        Transaction $transaction,
        bool $fromBiteship,
    ): Transaction {
        $completedTransaction = DB::transaction(function () use (
            $transaction,
            $fromBiteship,
        ) {
            $lockedTransaction = Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * Kalau sudah completed, jangan membuat history baru.
             * Tetapi tetap lanjut ke email supaya kegagalan email
             * sebelumnya dapat di-retry oleh webhook delivered berikutnya.
             */
            if (
                $fromBiteship
                && $lockedTransaction->status === Transaction::ORDER_COMPLETED
            ) {
                $this->sendCompletionEmailIfNeeded($lockedTransaction);

                return $lockedTransaction->fresh();
            }

            $allowed = $fromBiteship
                ? (
                    $lockedTransaction->shipping_method === 'Kurir'
                    && $lockedTransaction->status === Transaction::ORDER_SHIPPED
                )
                : (
                    (
                        $lockedTransaction->shipping_method === 'Ambil di Tempat'
                        && $lockedTransaction->status === Transaction::ORDER_PROCESSING
                    )
                    || (
                        $lockedTransaction->shipping_method === 'Kurir'
                        && $lockedTransaction->status === Transaction::ORDER_SHIPPED
                    )
                );

            if (! $allowed) {
                throw ValidationException::withMessages([
                    'transaction' => 'Status transaksi belum dapat diselesaikan.',
                ]);
            }

            $lockedTransaction->updateStatus(
                Transaction::ORDER_COMPLETED,
                $fromBiteship
                    ? 'Pesanan telah selesai berdasarkan status delivered dari Biteship.'
                    : 'Pesanan telah diselesaikan oleh admin.'
            );

            $this->sendCompletionEmailIfNeeded($lockedTransaction);

            return $lockedTransaction->fresh();
        });

        return $completedTransaction;
    }

    private function sendCompletionEmailIfNeeded(
        Transaction $transaction
    ): void {
        $notification = TransactionNotification::firstOrCreate([
            'transaction_id' => $transaction->id,
            'type' => 'ORDER_COMPLETED_EMAIL',
        ]);

        if ($notification->sent_at !== null) {
            return;
        }

        try {
            app(EmailArchiveService::class)->send($transaction->shipping_email, new OrderCompletedMail($transaction->fresh()));

            $notification->update([
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
