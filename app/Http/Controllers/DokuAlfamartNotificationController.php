<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\TransactionPaymentService;
use App\Services\TransactionExpiryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DokuAlfamartNotificationController extends Controller
{
    public function paymentNotification(
        Request $request,
        TransactionPaymentService $transactionPaymentService,
        TransactionExpiryService $transactionExpiryService
    ): JsonResponse {
        $rawBody = $request->getContent();

        $clientId = $request->header('Client-Id', '');
        $requestId = $request->header('Request-Id', '');
        $requestTimestamp = $request->header('Request-Timestamp', '');
        $signature = $request->header('Signature', '');

        /*
         * Required headers
         */
        if (
            blank($clientId)
            || blank($requestId)
            || blank($requestTimestamp)
            || blank($signature)
        ) {
            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Missing required headers.',
            ], 400);
        }

        /*
         * Client ID
         */
        if ($clientId !== (string) config('doku-minimarket.client_id')) {
            Log::warning('DOKU Alfamart notification client ID mismatch.', [
                'request_id' => $requestId,
            ]);

            return response()->json([
                'responseCode' => '4012500',
                'responseMessage' => 'Invalid client ID.',
            ], 401);
        }

        /*
         * Timestamp validation
         */
        $parsedTimestamp = \DateTimeImmutable::createFromFormat(
            'Y-m-d\TH:i:s\Z',
            $requestTimestamp
        );

        $timestampErrors = \DateTimeImmutable::getLastErrors();

        if (
            $parsedTimestamp === false
            || (
                $timestampErrors !== false
                && (
                    $timestampErrors['warning_count'] > 0
                    || $timestampErrors['error_count'] > 0
                )
            )
        ) {
            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Invalid timestamp.',
            ], 400);
        }

        /*
         * Non-SNAP signature verification
         */
        $requestTarget = $request->getPathInfo();

        $digest = base64_encode(
            hash('sha256', $rawBody, true)
        );

        $componentSignature =
            'Client-Id:' . $clientId . "\n"
            . 'Request-Id:' . $requestId . "\n"
            . 'Request-Timestamp:' . $requestTimestamp . "\n"
            . 'Request-Target:' . $requestTarget . "\n"
            . 'Digest:' . $digest;

        $secret = config('doku-minimarket.secret_key');

        if (blank($secret)) {
            Log::error(
                'DOKU Alfamart notification secret is not configured.'
            );

            return response()->json([
                'responseCode' => '5002500',
                'responseMessage' => 'Notification configuration error.',
            ], 500);
        }

        $expectedSignature = 'HMACSHA256=' . base64_encode(
            hash_hmac(
                'sha256',
                $componentSignature,
                $secret,
                true
            )
        );

        if (! hash_equals($expectedSignature, $signature)) {
            Log::warning('DOKU Alfamart notification signature mismatch.', [
                'request_id' => $requestId,
                'request_target' => $requestTarget,
            ]);

            return response()->json([
                'responseCode' => '4012500',
                'responseMessage' => 'Invalid signature.',
            ], 401);
        }

        /*
         * JSON payload
         */
        $payload = json_decode($rawBody, true);

        if (! is_array($payload)) {
            return response()->json([
                'responseCode' => '4002500',
                'responseMessage' => 'Invalid JSON payload.',
            ], 400);
        }

        /*
         * Alfamart channel validation
         */
        $serviceId = data_get($payload, 'service.id');
        $acquirerId = data_get($payload, 'acquirer.id');
        $channelId = data_get($payload, 'channel.id');

        if (
            $serviceId !== 'ONLINE_TO_OFFLINE'
            || $acquirerId !== 'ALFA'
            || $channelId !== 'ONLINE_TO_OFFLINE_ALFA'
        ) {
            Log::warning('DOKU Alfamart notification channel mismatch.', [
                'request_id' => $requestId,
                'service_id' => $serviceId,
                'acquirer_id' => $acquirerId,
                'channel_id' => $channelId,
            ]);

            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Invalid payment channel.',
            ], 400);
        }

        /*
         * Required payment data
         */
        $invoiceNumber = data_get(
            $payload,
            'order.invoice_number'
        );

        $paidAmount = data_get(
            $payload,
            'order.amount'
        );

        $transactionStatus = data_get(
            $payload,
            'transaction.status'
        );

        $transactionDate = data_get(
            $payload,
            'transaction.date'
        );

        $referenceNo = data_get(
            $payload,
            'transaction.original_request_id'
        );

        $paymentCode = data_get(
            $payload,
            'online_to_offline_info.payment_code'
        );

        if (
            blank($invoiceNumber)
            || blank($transactionStatus)
            || blank($transactionDate)
            || blank($referenceNo)
            || blank($paymentCode)
            || $paidAmount === null
        ) {
            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Invalid payment data.',
            ], 400);
        }

        /*
        * Alfamart amount must be an integer IDR value
        * with a maximum of 12 digits.
        */
        if (
            ! is_int($paidAmount)
            && ! is_string($paidAmount)
        ) {
            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Invalid payment amount.',
            ], 400);
        }

        $paidAmountString = (string) $paidAmount;

        if (! preg_match('/^\d{1,12}$/', $paidAmountString)) {
            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Invalid payment amount.',
            ], 400);
        }

        $paidAmount = (int) $paidAmountString;

        if ($paidAmount <= 0) {
            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Invalid payment amount.',
            ], 400);
        }

        /*
         * Only SUCCESS can transition a transaction to PAID.
         */
        if ($transactionStatus !== 'SUCCESS') {
            return response()->json([
                'responseCode' => '2002500',
                'responseMessage' => 'Notification received.',
            ]);
        }

        /*
         * Transaction lookup
         */
        $transaction = Transaction::query()
            ->where('invoice_number', $invoiceNumber)
            ->where('payment_method', 'ALFAMART')
            ->where('minimarket_store', 'ALFAMART')
            ->first();

        if (! $transaction) {
            Log::warning(
                'Transaction not found for DOKU Alfamart notification.',
                [
                    'request_id' => $requestId,
                    'invoice_number' => $invoiceNumber,
                ]
            );

            return response()->json([
                'responseCode' => '4042500',
                'responseMessage' => 'Transaction not found.',
            ], 404);
        }

        /*
         * Original Request-Id validation
         */
        $storedRequestId = (string) $transaction->minimarket_request_id;

        if (
            blank($storedRequestId)
            || ! hash_equals($storedRequestId, (string) $referenceNo)
        ) {
            Log::warning('DOKU Alfamart original request ID mismatch.', [
                'transaction_id' => $transaction->id,
                'invoice_number' => $invoiceNumber,
                'notification_request_id' => $requestId,
            ]);

            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Original request ID mismatch.',
            ], 400);
        }

        /*
         * Payment code validation
         */
        $storedPaymentCode = (string) $transaction->minimarket_payment_code;

        if (
            blank($storedPaymentCode)
            || ! hash_equals($storedPaymentCode, (string) $paymentCode)
        ) {
            Log::warning('DOKU Alfamart payment code mismatch.', [
                'transaction_id' => $transaction->id,
                'invoice_number' => $invoiceNumber,
                'request_id' => $requestId,
            ]);

            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Payment code mismatch.',
            ], 400);
        }

        /*
        * Amount validation using exact decimal string comparison.
        */
        $transactionTotal = (string) $transaction->total;

        if (! preg_match('/^(\d+)\.(\d{2})$/', $transactionTotal, $matches)) {
            Log::warning('DOKU Alfamart transaction total format is invalid.', [
                'transaction_id' => $transaction->id,
                'invoice_number' => $invoiceNumber,
            ]);

            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Invalid transaction amount.',
            ], 400);
        }

        if ($matches[2] !== '00') {
            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Transaction amount must be an integer.',
            ], 400);
        }

        $expectedAmount = ltrim($matches[1], '0') ?: '0';
        $notificationAmount = ltrim($paidAmountString, '0') ?: '0';

        if ($expectedAmount !== $notificationAmount) {
            Log::warning('DOKU Alfamart amount mismatch.', [
                'transaction_id' => $transaction->id,
                'invoice_number' => $invoiceNumber,
                'expected' => $expectedAmount,
                'received' => $notificationAmount,
            ]);

            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Payment amount mismatch.',
            ], 400);
        }

        /*
         * Normalize notification to the existing payment service contract.
         */
        $normalizedPaymentPayload = [
            'responseCode' => '2002600',
            'responseMessage' => 'Success',

            'paymentFlagReason' => [
                'english' => 'Success',
            ],

            'paidAmount' => [
                'value' => $paidAmount,
                'currency' => 'IDR',
            ],

            'paymentRequestId' => $referenceNo,

            'alfamartNotification' => $payload,
        ];

        $expiredDuringNotification = false;

        try {
            DB::transaction(function () use (
                $transaction,
                $normalizedPaymentPayload,
                $transactionPaymentService,
                $payload,
                &$expiredDuringNotification
            ) {
                $lockedTransaction = Transaction::query()
                    ->whereKey($transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                 * Duplicate notification: acknowledge without
                 * processing payment or stock again.
                 */
                if ($lockedTransaction->status === 'PAID') {
                    return;
                }

                /*
                * Alfamart expired transactions must not be recovered.
                */
                if ($lockedTransaction->status !== 'PENDING') {
                    throw ValidationException::withMessages([
                        'transaction' => [
                            'Transaction is not in a payable state.',
                        ],
                    ]);
                }

                /*
                * Prevent late Alfamart notifications from processing payment.
                */
                if (
                    $lockedTransaction->minimarket_expired_at
                    && ! $lockedTransaction->minimarket_expired_at->isFuture()
                ) {
                    $expiredDuringNotification = true;

                    return;
                }

                $transactionPaymentService->processSuccessfulPayment(
                    $lockedTransaction,
                    $normalizedPaymentPayload,
                    'DOKU Alfamart notification',
                    false
                );

                /*
                 * Preserve the original payment-code response
                 * and append the callback payload.
                 */
                $minimarketResponse = is_array(
                    $lockedTransaction->minimarket_response
                )
                    ? $lockedTransaction->minimarket_response
                    : [];

                $minimarketResponse['notification'] = $payload;

                $lockedTransaction->update([
                    'minimarket_response' => $minimarketResponse,
                ]);
            });

            if ($expiredDuringNotification) {
                if (! $transactionExpiryService->expire($transaction)) {
                    $transaction->refresh();

                    if ($transaction->status === 'PENDING') {
                        throw new \RuntimeException(
                            'Failed to expire overdue Alfamart transaction.'
                        );
                    }
                }

                Log::warning('DOKU Alfamart notification received after expiry.', [
                    'transaction_id' => $transaction->id,
                    'invoice_number' => $invoiceNumber,
                    'request_id' => $requestId,
                ]);

                return response()->json([
                    'responseCode' => '4092500',
                    'responseMessage' => 'Payment code has expired.',
                ], 409);
            }
        } catch (ValidationException $e) {
            Log::warning('DOKU Alfamart payment processing rejected.', [
                'transaction_id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'responseCode' => '4092500',
                'responseMessage' => 'Payment processing rejected.',
            ], 409);
        } catch (\Throwable $e) {
            Log::error('DOKU Alfamart payment processing failed.', [
                'transaction_id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'responseCode' => '5002500',
                'responseMessage' => 'Payment processing failed.',
            ], 500);
        }

        Log::info('DOKU Alfamart notification processed.', [
            'transaction_id' => $transaction->id,
            'invoice_number' => $invoiceNumber,
            'request_id' => $requestId,
            'amount' => $notificationAmount,
        ]);

        return response()->json([
            'responseCode' => '2002500',
            'responseMessage' => 'Success',
        ]);
    }
}
