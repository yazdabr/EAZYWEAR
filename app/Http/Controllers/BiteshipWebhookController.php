<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\BiteshipWebhookSignatureVerifier;
use App\Services\TransactionCompletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class BiteshipWebhookController extends Controller
{
    private const PROGRESS_STATUSES = [
        'confirmed' => 10,
        'scheduled' => 20,
        'allocated' => 30,
        'picking_up' => 40,
        'picked' => 50,
        'in_transit' => 60,
        'dropping_off' => 70,
    ];

    private const TERMINAL_STATUSES = [
        'cancelled',
        'returned',
        'rejected',
        'courier_not_found',
        'disposed',
    ];

    public function __construct(
        private BiteshipWebhookSignatureVerifier $signatureVerifier,
        private TransactionCompletionService $completionService,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        try {
            /*
             * Biteship installation handshake.
             *
             * Jangan memerlukan signature untuk request kosong
             * yang digunakan saat konfigurasi webhook.
             */
            if (
                $request->isMethod('POST')
                && str_contains(
                    strtolower((string) $request->header('Content-Type')),
                    'application/json'
                )
                && trim($request->getContent()) === ''
            ) {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook endpoint ready.',
                ]);
            }

            if (! $this->signatureVerifier->verify($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Webhook signature tidak valid.',
                ], 401);
            }

            $payload = $request->json()->all();

            if (($payload['event'] ?? null) !== 'order.status') {
                return response()->json([
                    'success' => false,
                    'message' => 'Event webhook tidak didukung.',
                ], 422);
            }

            $orderId = $payload['order_id'] ?? null;
            $status = $payload['status'] ?? null;

            if (
                ! is_string($orderId)
                || trim($orderId) === ''
                || ! is_string($status)
                || trim($status) === ''
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payload webhook tidak lengkap.',
                ], 422);
            }

            $status = strtolower(trim($status));

            if (! $this->isSupportedStatus($status)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status Biteship tidak didukung.',
                ], 422);
            }

            $transaction = DB::transaction(function () use (
                $orderId,
                $status,
                $payload,
            ) {
                $transaction = Transaction::query()
                    ->where('biteship_order_id', $orderId)
                    ->lockForUpdate()
                    ->first();

                if (! $transaction) {
                    return null;
                }

                if ($this->shouldIgnoreStatus(
                    $transaction->biteship_status,
                    $status
                )) {
                    return $transaction->fresh();
                }

                $updates = [
                    'biteship_status' => $status,
                ];

                if (
                    isset($payload['courier_tracking_id'])
                    && is_string($payload['courier_tracking_id'])
                    && $payload['courier_tracking_id'] !== ''
                ) {
                    $updates['biteship_tracking_id']
                        = $payload['courier_tracking_id'];
                }

                if (
                    isset($payload['courier_waybill_id'])
                    && is_string($payload['courier_waybill_id'])
                    && $payload['courier_waybill_id'] !== ''
                ) {
                    $updates['biteship_waybill_id']
                        = $payload['courier_waybill_id'];
                }

                if (
                    isset($payload['courier_company'])
                    && is_string($payload['courier_company'])
                    && $payload['courier_company'] !== ''
                ) {
                    $updates['courier'] = $payload['courier_company'];
                }

                if (
                    empty($transaction->tracking_number)
                    && ! empty($updates['biteship_waybill_id'])
                ) {
                    $updates['tracking_number']
                        = $updates['biteship_waybill_id'];
                }

                $transaction->update($updates);

                return $transaction->fresh();
            });

            if (! $transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi dengan Biteship order ID tersebut tidak ditemukan.',
                ], 404);
            }

            /*
             * Completion dilakukan setelah provider status berhasil
             * disimpan.
             *
             * TransactionCompletionService sendiri menangani:
             * - lockForUpdate
             * - ORDER_SHIPPED -> ORDER_COMPLETED
             * - duplicate delivered
             * - retry completion email
             */
            if (
                $status === 'delivered'
                && $transaction->biteship_status === 'delivered'
            ) {
                $transaction = $this->completionService
                    ->completeFromBiteship($transaction);
            }

            return response()->json([
                'success' => true,
                'message' => 'Webhook diterima.',
                'order_id' => $orderId,
                'status' => $transaction->biteship_status,
                'transaction_status' => $transaction->status,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Webhook belum dapat diproses.',
            ], 503);
        }
    }

    private function isSupportedStatus(string $status): bool
    {
        return isset(self::PROGRESS_STATUSES[$status])
            || $status === 'on_hold'
            || $status === 'delivered'
            || in_array($status, self::TERMINAL_STATUSES, true)
            || $status === 'return_in_transit';
    }

    private function shouldIgnoreStatus(
        ?string $currentStatus,
        string $incomingStatus,
    ): bool {
        if (! $currentStatus || $currentStatus === $incomingStatus) {
            return false;
        }

        /*
         * delivered adalah terminal success.
         * Status webhook lama tidak boleh menurunkannya.
         */
        if ($currentStatus === 'delivered') {
            return true;
        }

        /*
         * Setelah return selesai, jangan mundur lagi ke
         * return_in_transit atau status pengiriman normal.
         */
        if ($currentStatus === 'returned') {
            return true;
        }

        if ($currentStatus === 'return_in_transit') {
            return $incomingStatus !== 'returned';
        }

        /*
         * Status terminal provider lainnya dianggap final untuk
         * lifecycle provider. Kita tidak mengubahnya otomatis.
         */
        if (in_array($currentStatus, self::TERMINAL_STATUSES, true)) {
            return true;
        }

        /*
         * returned hanya boleh dicapai dari return_in_transit
         * atau diterima sebagai terminal baru.
         */
        if ($incomingStatus === 'return_in_transit') {
            return false;
        }

        /*
         * Progress normal menggunakan urutan eksplisit.
         */
        $currentRank = self::PROGRESS_STATUSES[$currentStatus] ?? null;
        $incomingRank = self::PROGRESS_STATUSES[$incomingStatus] ?? null;

        if ($currentRank !== null && $incomingRank !== null) {
            return $incomingRank < $currentRank;
        }

        /*
         * on_hold dapat muncul sebagai kondisi khusus.
         * Jangan menganggapnya sebagai rank linear.
         */
        if ($currentStatus === 'on_hold') {
            return false;
        }

        if ($incomingStatus === 'on_hold') {
            return false;
        }

        return false;
    }
}