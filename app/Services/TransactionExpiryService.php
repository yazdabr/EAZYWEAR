<?php

namespace App\Services;

use App\Models\Transaction;
use App\Mail\PaymentExpiredMail;
use App\Models\FulfillmentHold;
use App\Services\EmailArchiveService;
use App\Services\FulfillmentHoldService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionExpiryService
{

    public function __construct(
        private readonly FulfillmentHoldService $fulfillmentHoldService,
    ) {
    }
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

            $expiredAt = $lockedTransaction->payment_method === 'QRIS'
                ? $lockedTransaction->qris_expired_at
                : $lockedTransaction->va_expired_at;

            if (!$expiredAt) {
                return false;
            }

            if ($expiredAt->isFuture()) {
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
            $expiredTransaction = Transaction::findOrFail($transaction->id);

            Log::info('TRANSACTION EXPIRED', [
                'transaction_id' => $expiredTransaction->id,
                'invoice' => $expiredTransaction->invoice_number,
                'payment_method' => $expiredTransaction->payment_method,
                'expired_at' => $expiredTransaction->payment_method === 'QRIS'
                    ? $expiredTransaction->qris_expired_at
                    : $expiredTransaction->va_expired_at,
            ]);

            if (
                $expiredTransaction->fulfillmentHold()
                    ->where('status', FulfillmentHold::HELD)
                    ->exists()
            ) {
                $this->fulfillmentHoldService->releaseHold($expiredTransaction);
            }

            app(EmailArchiveService::class)->send($expiredTransaction->shipping_email, new PaymentExpiredMail($expiredTransaction));
        }


        return $expired;
    }
}
