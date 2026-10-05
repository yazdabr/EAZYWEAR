<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\TransactionPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DokuQrisNotificationController extends Controller
{
    public function paymentNotification(
        Request $request,
        TransactionPaymentService $transactionPaymentService
    ): JsonResponse {
        $rawBody = $request->getContent();

        $clientId = $request->header('Client-Id', '');
        $requestId = $request->header('Request-Id', '');
        $requestTimestamp = $request->header('Request-Timestamp', '');
        $signature = $request->header('Signature', '');

        /*
         * ------------------------------------------------------------
         * Required headers
         * ------------------------------------------------------------
         */
        if (
            blank($clientId)
            || blank($requestId)
            || blank($requestTimestamp)
            || blank($signature)
        ) {
            Log::warning('DOKU QRIS notification missing required headers.', [
                'request_id' => $requestId,
            ]);

            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Missing required headers.',
            ], 400);
        }

        /*
         * ------------------------------------------------------------
         * Client ID
         * ------------------------------------------------------------
         */
        if ($clientId !== (string) config('doku-qris.client_id')) {
            Log::warning('DOKU QRIS notification client ID mismatch.', [
                'request_id' => $requestId,
            ]);

            return response()->json([
                'responseCode' => '4012500',
                'responseMessage' => 'Invalid client ID.',
            ], 401);
        }

        /*
         * ------------------------------------------------------------
         * Validate timestamp format
         * ------------------------------------------------------------
         */
        try {
            $parsedTimestamp = \DateTimeImmutable::createFromFormat(
                'Y-m-d\TH:i:s\Z',
                $requestTimestamp
            );

            if ($parsedTimestamp === false) {
                throw new \RuntimeException(
                    'Invalid timestamp.'
                );
            }

            $errors = \DateTimeImmutable::getLastErrors();

            if (
                $errors !== false
                && (
                    $errors['warning_count'] > 0
                    || $errors['error_count'] > 0
                )
            ) {
                throw new \RuntimeException(
                    'Invalid timestamp.'
                );
            }
        } catch (\Throwable) {
            Log::warning(
                'DOKU QRIS notification invalid timestamp.',
                [
                    'request_id' => $requestId,
                ]
            );

            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Invalid timestamp.',
            ], 400);
        }

        /*
         * ------------------------------------------------------------
         * Signature verification
         *
         * DOKU Non-SNAP HTTP Notification:
         *
         * Client-Id
         * Request-Id
         * Request-Timestamp
         * Request-Target
         * Digest
         * ------------------------------------------------------------
         */
        $requestTarget = $request->getPathInfo();

        $digest = base64_encode(
            hash(
                'sha256',
                $rawBody,
                true
            )
        );

        $componentSignature =
            'Client-Id:' . $clientId . "\n"
            . 'Request-Id:' . $requestId . "\n"
            . 'Request-Timestamp:' . $requestTimestamp . "\n"
            . 'Request-Target:' . $requestTarget . "\n"
            . 'Digest:' . $digest;

        $secret = config(
            'doku-qris.notification_secret'
        );

        if (blank($secret)) {
            Log::error(
                'DOKU QRIS notification secret is not configured.'
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
            Log::warning(
                'DOKU QRIS notification signature mismatch.',
                [
                    'request_id' => $requestId,
                    'request_target' => $requestTarget,
                ]
            );

            return response()->json([
                'responseCode' => '4012500',
                'responseMessage' => 'Invalid signature.',
            ], 401);
        }

        /*
         * ------------------------------------------------------------
         * JSON
         * ------------------------------------------------------------
         */
        $payload = json_decode(
            $rawBody,
            true
        );

        if (! is_array($payload)) {
            return response()->json([
                'responseCode' => '4002500',
                'responseMessage' => 'Invalid JSON payload.',
            ], 400);
        }

        /*
         * ------------------------------------------------------------
         * QRIS channel validation
         * ------------------------------------------------------------
         */
        $serviceId = data_get(
            $payload,
            'service.id'
        );

        $channelId = data_get(
            $payload,
            'channel.id'
        );

        if ($serviceId !== 'QRIS') {
            Log::warning(
                'DOKU QRIS notification has invalid service.',
                [
                    'request_id' => $requestId,
                    'service_id' => $serviceId,
                ]
            );

            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Invalid service.',
            ], 400);
        }

        if ($channelId !== 'QRIS_DOKU') {
            Log::warning(
                'DOKU QRIS notification has invalid channel.',
                [
                    'request_id' => $requestId,
                    'channel_id' => $channelId,
                ]
            );

            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Invalid channel.',
            ], 400);
        }

        /*
         * ------------------------------------------------------------
         * Payment data
         * ------------------------------------------------------------
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

        if (
            blank($invoiceNumber)
            || $paidAmount === null
            || blank($transactionStatus)
        ) {
            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Invalid payment data.',
            ], 400);
        }

        /*
         * DOKU notification can contain FAILED.
         * For this checkout flow, only SUCCESS can transition
         * a transaction to PAID.
         */
        if ($transactionStatus !== 'SUCCESS') {
            return response()->json([
                'responseCode' => '2002500',
                'responseMessage' => 'Notification received.',
            ]);
        }

        /*
         * ------------------------------------------------------------
         * Transaction lookup
         * ------------------------------------------------------------
         */
        $transaction = Transaction::query()
            ->where('invoice_number', $invoiceNumber)
            ->where('payment_method', 'QRIS')
            ->first();

        if (! $transaction) {
            Log::warning(
                'Transaction not found for DOKU QRIS notification.',
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
         * ------------------------------------------------------------
         * Amount validation
         * ------------------------------------------------------------
         */
        $transactionAmount = number_format(
            (float) $transaction->total,
            2,
            '.',
            ''
        );

        $notificationAmount = number_format(
            (float) $paidAmount,
            2,
            '.',
            ''
        );

        if ($transactionAmount !== $notificationAmount) {
            Log::warning(
                'DOKU QRIS amount mismatch.',
                [
                    'transaction_id' => $transaction->id,
                    'invoice_number' => $invoiceNumber,
                    'expected' => $transactionAmount,
                    'received' => $notificationAmount,
                ]
            );

            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Payment amount mismatch.',
            ], 400);
        }

        /*
         * ------------------------------------------------------------
         * Normalize QRIS notification to the existing payment service
         * contract.
         * ------------------------------------------------------------
         */
        $referenceNo = data_get(
            $payload,
            'transaction.original_request_id'
        );

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

            'paymentRequestId' => $referenceNo
                ?: $requestId,

            'qrisNotification' => $payload,
        ];

        try {
            DB::transaction(
                function () use (
                    $transaction,
                    $normalizedPaymentPayload,
                    $transactionPaymentService,
                    $payload
                ) {
                    $lockedTransaction = Transaction::query()
                        ->whereKey($transaction->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($lockedTransaction->status === 'PAID') {
                        return;
                    }

                    $transactionPaymentService->processSuccessfulPayment(
                        $lockedTransaction,
                        $normalizedPaymentPayload,
                        'DOKU QRIS notification'
                    );

                    $lockedTransaction->update([
                        'qris_reference_no' =>
                            data_get(
                                $payload,
                                'transaction.original_request_id'
                            ),

                        'qris_response' => $payload,
                    ]);
                }
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning(
                'DOKU QRIS payment processing rejected.',
                [
                    'transaction_id' => $transaction->id,
                    'invoice_number' => $transaction->invoice_number,
                    'errors' => $e->errors(),
                ]
            );

            return response()->json([
                'responseCode' => '4092500',
                'responseMessage' => 'Payment processing rejected.',
            ], 409);
        } catch (\Throwable $e) {
            Log::error(
                'DOKU QRIS payment processing failed.',
                [
                    'transaction_id' => $transaction->id,
                    'invoice_number' => $transaction->invoice_number,
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'responseCode' => '5002500',
                'responseMessage' => 'Payment processing failed.',
            ], 500);
        }

        Log::info(
            'DOKU QRIS notification processed.',
            [
                'transaction_id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'request_id' => $requestId,
                'amount' => $notificationAmount,
            ]
        );

        return response()->json([
            'responseCode' => '2002500',
            'responseMessage' => 'Success',
        ]);
    }
}