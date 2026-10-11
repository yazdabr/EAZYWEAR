<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionNotification;
use App\Mail\PaymentConfirmedMail;
use App\Models\FulfillmentHold;
use App\Services\EmailArchiveService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionPaymentService
{
    public function __construct(
        private readonly InventoryStockService $inventoryStockService,
        private readonly FulfillmentHoldService $fulfillmentHoldService,
    ) {
    }

    public function processSuccessfulPayment(
        Transaction $transaction,
        array $dokuResponse,
        string $source,
        bool $allowExpiredRecovery = false
    ): void {
        $responseCode = $dokuResponse['responseCode'] ?? null;

        $paymentFlagReason = data_get(
            $dokuResponse,
            'virtualAccountData.paymentFlagReason.english'
        ) ?: data_get($dokuResponse, 'paymentFlagReason.english');

        $paidAmount = data_get(
            $dokuResponse,
            'virtualAccountData.paidAmount.value'
        ) ?? data_get($dokuResponse, 'paidAmount.value');

        if (
            $responseCode !== '2002600'
            || strtolower((string) $paymentFlagReason) !== 'success'
            || $paidAmount === null
            || $paidAmount === ''
        ) {
            throw ValidationException::withMessages([
                'payment' => 'Pembayaran DOKU belum terverifikasi sebagai pembayaran berhasil.',
            ]);
        }

        $paymentRequestId = data_get(
            $dokuResponse,
            'virtualAccountData.paymentRequestId'
        ) ?: data_get($dokuResponse, 'paymentRequestId');

        $transactionId = $transaction->getKey();

        DB::transaction(function () use (
            $transactionId,
            $dokuResponse,
            $source,
            $allowExpiredRecovery,
            $paidAmount,
            $paymentRequestId
        ): void {
            $lockedTransaction = Transaction::query()
                ->whereKey($transactionId)
                ->lockForUpdate()
                ->firstOrFail();

            $transactionAmount = number_format(
                (float) $lockedTransaction->total,
                2,
                '.',
                ''
            );

            $dokuAmount = number_format(
                (float) $paidAmount,
                2,
                '.',
                ''
            );

            if ($transactionAmount !== $dokuAmount) {
                Log::warning('DOKU PAYMENT AMOUNT MISMATCH', [
                    'transaction_id' => $lockedTransaction->id,
                    'invoice' => $lockedTransaction->invoice_number,
                    'transaction_amount' => $transactionAmount,
                    'doku_amount' => $dokuAmount,
                    'source' => $source,
                ]);

                throw ValidationException::withMessages([
                    'payment' => 'Nominal pembayaran DOKU tidak sesuai dengan nominal transaksi.',
                ]);
            }

            // Idempoten: jangan potong stok atau membuat alokasi ulang.
            if ($lockedTransaction->status === 'PAID') {
                return;
            }

            $canRecoverExpired = $allowExpiredRecovery
                && strtoupper((string) $lockedTransaction->payment_method) === 'QRIS'
                && $lockedTransaction->status === 'EXPIRED';

            if (
                $lockedTransaction->status !== 'PENDING'
                && ! $canRecoverExpired
            ) {
                throw ValidationException::withMessages([
                    'payment' => 'Transaksi tidak dapat diproses menjadi PAID.',
                ]);
            }

            // Operasi ini memiliki transaksi DB sendiri; Laravel akan
            // menggabungkannya sebagai nested transaction/savepoint.
            $this->inventoryStockService->decreaseForTransaction(
                $lockedTransaction,
                "{$source} - {$lockedTransaction->invoice_number}",
                $canRecoverExpired
            );

            $lockedTransaction->update([
                'status' => 'PAID',
                'paid_at' => $lockedTransaction->paid_at ?: now(),
                'doku_payment_id' => $paymentRequestId
                    ?: $lockedTransaction->doku_payment_id,
                'doku_response' => $dokuResponse,
            ]);

            $lockedTransaction->refresh();

            try {
                $hold = $lockedTransaction->fulfillmentHold()->first();

                if ($hold && $hold->status === FulfillmentHold::HELD) {
                    $this->fulfillmentHoldService->convertHoldToAllocation(
                        $lockedTransaction
                    );
                } elseif (
                    $canRecoverExpired
                    && $hold
                    && $hold->status === FulfillmentHold::RELEASED
                ) {
                    $this->fulfillmentHoldService
                        ->restoreReleasedHoldForPaidTransaction(
                            $lockedTransaction
                        );
                } elseif (! $hold) {
                    Log::warning(
                        'Payment confirmed but fulfillment hold is missing.',
                        [
                            'transaction_id' => $lockedTransaction->id,
                            'invoice' => $lockedTransaction->invoice_number,
                        ]
                    );
                }
            } catch (\Throwable $e) {
                Log::error(
                    'Payment confirmed but fulfillment allocation needs attention.',
                    [
                        'transaction_id' => $lockedTransaction->id,
                        'invoice' => $lockedTransaction->invoice_number,
                        'error' => $e->getMessage(),
                    ]
                );
            }

            $lockedTransaction->addStatusHistory(
                Transaction::PAYMENT_CONFIRMED,
                "Pembayaran berhasil dikonfirmasi melalui {$source}."
            );
        });

        // Kirim email hanya setelah transaksi DB berhasil commit.
        $freshTransaction = Transaction::query()->findOrFail($transactionId);

        if ($freshTransaction->status === 'PAID') {
            $this->sendPaymentConfirmationEmailIfNeeded($freshTransaction);
        }

        Log::info('DOKU PAYMENT PROCESSED', [
            'transaction_id' => $freshTransaction->id,
            'invoice' => $freshTransaction->invoice_number,
            'source' => $source,
            'payment_request_id' => $paymentRequestId,
            'amount' => number_format((float) $paidAmount, 2, '.', ''),
        ]);
    }

    private function sendPaymentConfirmationEmailIfNeeded(
        Transaction $transaction
    ): void {
        $notification = TransactionNotification::firstOrCreate([
            'transaction_id' => $transaction->id,
            'type' => 'PAYMENT_CONFIRMED_EMAIL',
        ]);

        if ($notification->sent_at !== null) {
            return;
        }

        try {
            app(EmailArchiveService::class)->send(
                $transaction->shipping_email,
                new PaymentConfirmedMail($transaction->fresh())
            );

            $notification->update([
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
