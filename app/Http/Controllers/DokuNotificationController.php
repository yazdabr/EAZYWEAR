<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DokuNotificationController extends Controller
{
    public function bcaPayment(Request $request): JsonResponse
    {
        $rawBody = $request->getContent();
        $timestamp = $request->header('X-TIMESTAMP', '');
        $signature = $request->header('X-SIGNATURE', '');
        $partnerId = $request->header('X-PARTNER-ID', '');
        $externalId = $request->header('X-EXTERNAL-ID', '');
        $channelId = $request->header('CHANNEL-ID', '');
        $authorization = $request->header('Authorization', '');

        if (!$timestamp || !$signature || !$partnerId || !$externalId || !$channelId || !$authorization) {
            Log::warning('DOKU notification missing required headers.', [
                'external_id' => $externalId,
                'partner_id' => $partnerId,
                'channel_id' => $channelId,
            ]);

            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Missing required headers.',
            ], 400);
        }

        if ($partnerId !== (string) config('doku.client_id')) {
            Log::warning('DOKU notification partner ID mismatch.', [
                'partner_id' => $partnerId,
            ]);

            return response()->json([
                'responseCode' => '4012500',
                'responseMessage' => 'Invalid partner ID.',
            ], 401);
        }

        $accessToken = preg_replace(
            '/^Bearer\s+/i',
            '',
            trim($authorization)
        );

        $endpoint = $request->getPathInfo();
        $bodyHash = strtolower(hash('sha256', $rawBody));

        $stringToSign = implode(':', [
            'POST',
            $endpoint,
            $accessToken,
            $bodyHash,
            $timestamp,
        ]);

        $expectedSignature = base64_encode(
            hash_hmac(
                'sha512',
                $stringToSign,
                config('doku.secret_key'),
                true
            )
        );

        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('DOKU notification signature mismatch.', [
                'endpoint' => $endpoint,
                'partner_id' => $partnerId,
                'external_id' => $externalId,
            ]);

            return response()->json([
                'responseCode' => '4012500',
                'responseMessage' => 'Invalid signature.',
            ], 401);
        }

        $payload = json_decode($rawBody, true);

        if (!is_array($payload)) {
            return response()->json([
                'responseCode' => '4002500',
                'responseMessage' => 'Invalid JSON payload.',
            ], 400);
        }

        $trxId = $payload['trxId'] ?? null;
        $paymentRequestId = $payload['paymentRequestId'] ?? null;
        $virtualAccountNo = $payload['virtualAccountNo'] ?? null;
        $paidAmount = $payload['paidAmount']['value'] ?? null;
        $currency = $payload['paidAmount']['currency'] ?? null;

        if (!$virtualAccountNo || $paidAmount === null || $currency !== 'IDR') {
            Log::warning('DOKU notification has invalid payment data.', [
                'external_id' => $externalId,
                'payload' => $payload,
            ]);

            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Invalid payment data.',
            ], 400);
        }

        $normalizedVa = preg_replace('/\s+/', '', (string) $virtualAccountNo);

        $transaction = Transaction::query()
            ->whereNotNull('va_number')
            ->get()
            ->first(function (Transaction $item) use ($normalizedVa, $trxId) {
                $storedVa = preg_replace(
                    '/\s+/',
                    '',
                    (string) $item->va_number
                );

                return $storedVa === $normalizedVa
                    || ($trxId && $item->invoice_number === $trxId);
            });

        if (!$transaction) {
            Log::warning('Transaction not found for DOKU notification.', [
                'virtual_account_no' => $normalizedVa,
                'trx_id' => $trxId,
                'payment_request_id' => $paymentRequestId,
            ]);

            return response()->json([
                'responseCode' => '4042500',
                'responseMessage' => 'Transaction not found.',
            ], 404);
        }

        $expectedAmount = number_format(
            (float) $transaction->total,
            2,
            '.',
            ''
        );

        $receivedAmount = number_format(
            (float) $paidAmount,
            2,
            '.',
            ''
        );

        if ($expectedAmount !== $receivedAmount) {
            Log::warning('DOKU notification amount mismatch.', [
                'transaction_id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'expected_amount' => $expectedAmount,
                'received_amount' => $receivedAmount,
            ]);

            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Payment amount mismatch.',
            ], 400);
        }

        if ($transaction->status !== 'PAID') {
            $transaction->update([
                'status' => 'PAID',
                'paid_at' => now(),
                'doku_payment_id' => $paymentRequestId,
                'doku_response' => $payload,
            ]);
        }

        Log::info('DOKU BCA payment notification processed.', [
            'transaction_id' => $transaction->id,
            'invoice_number' => $transaction->invoice_number,
            'payment_request_id' => $paymentRequestId,
            'paid_amount' => $paidAmount,
            'external_id' => $externalId,
        ]);

        return response()->json([
            'responseCode' => '2002500',
            'responseMessage' => 'Success',
        ]);
    }
}