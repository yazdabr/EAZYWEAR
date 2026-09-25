<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class TransactionPaymentService
{
    public function __construct(
        private readonly InventoryStockService $inventoryStockService,
    ) {
    }

    public function processSuccessfulPayment(
        Transaction $transaction,
        array $dokuResponse,
        string $source
    ): void {
        $responseCode = $dokuResponse['responseCode'] ?? null;

        $paymentFlagReason = data_get(
            $dokuResponse,
            'virtualAccountData.paymentFlagReason.english'
        );

        if (!$paymentFlagReason) {
            $paymentFlagReason = data_get(
                $dokuResponse,
                'paymentFlagReason.english'
            );
        }

        $paidAmount = data_get(
            $dokuResponse,
            'virtualAccountData.paidAmount.value'
        );

        if ($paidAmount === null) {
            $paidAmount = data_get(
                $dokuResponse,
                'paidAmount.value'
            );
        }

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

        $transactionAmount = number_format(
            (float) $transaction->total,
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
                'transaction_id' => $transaction->id,
                'invoice' => $transaction->invoice_number,
                'transaction_amount' => $transactionAmount,
                'doku_amount' => $dokuAmount,
                'source' => $source,
            ]);

            throw ValidationException::withMessages([
                'payment' => 'Nominal pembayaran DOKU tidak sesuai dengan nominal transaksi.',
            ]);
        }

        if ($transaction->status === 'PAID') {
            return;
        }

        if ($transaction->status !== 'PENDING') {
            throw ValidationException::withMessages([
                'payment' => 'Transaksi tidak dapat diproses menjadi PAID.',
            ]);
        }

        $paymentRequestId = data_get(
            $dokuResponse,
            'virtualAccountData.paymentRequestId'
        );

        if (!$paymentRequestId) {
            $paymentRequestId = data_get(
                $dokuResponse,
                'paymentRequestId'
            );
        }

        $this->inventoryStockService->decreaseForTransaction(
            $transaction,
            "{$source} - {$transaction->invoice_number}"
        );

        $transaction->update([
            'status' => 'PAID',
            'paid_at' => now(),
            'doku_payment_id' => $paymentRequestId ?: $transaction->doku_payment_id,
            'doku_response' => $dokuResponse,
        ]);

        $transaction->addStatusHistory(
            Transaction::PAYMENT_CONFIRMED,
            "Pembayaran berhasil dikonfirmasi melalui {$source}."
        );

        Log::info('DOKU PAYMENT PROCESSED', [
            'transaction_id' => $transaction->id,
            'invoice' => $transaction->invoice_number,
            'source' => $source,
            'payment_request_id' => $paymentRequestId,
            'amount' => $dokuAmount,
        ]);
    }
}