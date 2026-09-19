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

        if (
            !$timestamp ||
            !$signature ||
            !$partnerId ||
            !$externalId ||
            !$channelId ||
            !$authorization
        ) {
            Log::warning('DOKU notification missing required headers.', [
                'headers' => [
                    'X-TIMESTAMP' => $timestamp,
                    'X-PARTNER-ID' => $partnerId,
                    'X-EXTERNAL-ID' => $externalId,
                    'CHANNEL-ID' => $channelId,
                ],
            ]);

            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Missing required headers.',
            ], 400);
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
        $paidAmount = $payload['paidAmount']['value'] ?? null;
        $currency = $payload['paidAmount']['currency'] ?? null;

        if (!$trxId || $paidAmount === null || $currency !== 'IDR') {
            Log::warning('DOKU notification has invalid payment data.', [
                'payload' => $payload,
            ]);

            return response()->json([
                'responseCode' => '4002502',
                'responseMessage' => 'Invalid payment data.',
            ], 400);
        }

        $transaction = Transaction::query()
            ->where('invoice_number', $trxId)
            ->first();

        if (!$transaction) {
            Log::warning('Transaction not found for DOKU notification.', [
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
                'trx_id' => $trxId,
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
        ]);

        return response()->json([
            'responseCode' => '2002500',
            'responseMessage' => 'Success',
        ]);
    }
}