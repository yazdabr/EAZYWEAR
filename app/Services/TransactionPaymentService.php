<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionNotification;
use App\Mail\PaymentConfirmedMail;
use App\Models\FulfillmentHold;
use App\Services\EmailArchiveService;
use Illuminate\Support\Facades\Log;
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
            $this->sendPaymentConfirmationEmailIfNeeded($transaction->fresh());

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

        $transaction->refresh();

        if (
            $transaction->fulfillmentHold()
                ->where('status', FulfillmentHold::HELD)
                ->exists()
        ) {
            $this->fulfillmentHoldService->convertHoldToAllocation($transaction);
        }

        $transaction->addStatusHistory(
            Transaction::PAYMENT_CONFIRMED,
            "Pembayaran berhasil dikonfirmasi melalui {$source}."
        );

        $this->sendPaymentConfirmationEmailIfNeeded($transaction->fresh());

        Log::info('DOKU PAYMENT PROCESSED', [
            'transaction_id' => $transaction->id,
            'invoice' => $transaction->invoice_number,
            'source' => $source,
            'payment_request_id' => $paymentRequestId,
            'amount' => $dokuAmount,
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
