<?php

namespace App\Services;

use App\Models\Transaction;
use App\Mail\PaymentExpiredMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TransactionExpiryService
{
    /**
     * Mengubah transaksi PENDING yang sudah melewati
     * batas pembayaran menjadi EXPIRED.
     *
     * Tidak ada stock yang direstore karena transaksi
     * PENDING belum pernah melakukan deduct stock.
     */
    public function expire(Transaction $transaction): bool
    {
        $expired = DB::transaction(function () use ($transaction) {
            $lockedTransaction = Transaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTransaction->status !== 'PENDING') {
                return false;
            }

            if (!$lockedTransaction->va_expired_at) {
                return false;
            }

            if ($lockedTransaction->va_expired_at->isFuture()) {
                return false;
            }

            $lockedTransaction->update([
                'status' => 'EXPIRED',
            ]);

            $lockedTransaction->addStatusHistory(
                'EXPIRED',
                'Pembayaran melewati batas waktu pembayaran.'
            );

            return true;
        });


        if ($expired) {
            $expiredTransaction = Transaction::find($transaction->id);

            Mail::to($expiredTransaction->shipping_email)
                ->send(new PaymentExpiredMail($expiredTransaction));
        }


        return $expired;
    }
}