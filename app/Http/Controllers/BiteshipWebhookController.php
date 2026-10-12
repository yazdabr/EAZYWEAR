<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionFulfillment;
use App\Services\BiteshipWebhookSignatureVerifier;
use App\Services\TransactionCompletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class BiteshipWebhookController extends Controller
{
    private const PROGRESS_STATUSES = [
        'confirmed' => 10, 'scheduled' => 20, 'allocated' => 30,
        'picking_up' => 40, 'picked' => 50, 'in_transit' => 60,
        'dropping_off' => 70,
    ];

    private const TERMINAL_STATUSES = [
        'cancelled', 'returned', 'rejected', 'courier_not_found', 'disposed',
    ];

    public function __construct(
        private BiteshipWebhookSignatureVerifier $signatureVerifier,
        private TransactionCompletionService $completionService,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        try {
            if (
                $request->isMethod('POST')
                && str_contains(strtolower((string) $request->header('Content-Type')), 'application/json')
                && trim($request->getContent()) === ''
            ) {
                return response()->json(['success' => true, 'message' => 'Webhook endpoint ready.']);
            }

            if (! $this->signatureVerifier->verify($request)) {
                return response()->json(['success' => false, 'message' => 'Webhook signature tidak valid.'], 401);
            }

            $payload = $request->json()->all();
            if (($payload['event'] ?? null) !== 'order.status') {
                return response()->json(['success' => false, 'message' => 'Event webhook tidak didukung.'], 422);
            }

            $orderId = $payload['order_id'] ?? null;
            $status = $payload['status'] ?? null;
            if (! is_string($orderId) || trim($orderId) === '' || ! is_string($status) || trim($status) === '') {
                return response()->json(['success' => false, 'message' => 'Payload webhook tidak lengkap.'], 422);
            }

            $orderId = trim($orderId);
            $status = strtolower(trim($status));
            if (! $this->isSupportedStatus($status)) {
                return response()->json(['success' => false, 'message' => 'Status Biteship tidak didukung.'], 422);
            }

            // Fulfillment-specific order IDs take precedence; legacy transactions remain supported.
            $fulfillment = DB::transaction(function () use ($orderId, $status, $payload) {
                $row = TransactionFulfillment::query()
                    ->where('biteship_order_id', $orderId)
                    ->lockForUpdate()
                    ->first();

                if (! $row) {
                    return null;
                }

                if ($this->shouldIgnoreStatus($row->biteship_status, $status)) {
                    return $row->fresh();
                }

                $updates = ['biteship_status' => $status];
                foreach ([
                    'courier_tracking_id' => 'biteship_tracking_id',
                    'courier_waybill_id' => 'biteship_waybill_id',
                    'courier_company' => 'courier',
                ] as $source => $target) {
                    if (isset($payload[$source]) && is_string($payload[$source]) && trim($payload[$source]) !== '') {
                        $updates[$target] = trim($payload[$source]);
                    }
                }
                if (empty($row->tracking_number) && ! empty($updates['biteship_waybill_id'])) {
                    $updates['tracking_number'] = $updates['biteship_waybill_id'];
                }
                if ($status === 'delivered') {
                    $updates['status'] = TransactionFulfillment::STATUS_COMPLETED;
                } elseif (! in_array($status, self::TERMINAL_STATUSES, true) && $row->status !== TransactionFulfillment::STATUS_COMPLETED) {
                    $updates['status'] = in_array($status, ['picked', 'in_transit', 'dropping_off'], true)
                        ? TransactionFulfillment::STATUS_SHIPPED
                        : TransactionFulfillment::STATUS_PROCESSING;
                }
                $row->update($updates);

                return $row->fresh();
            });

            if ($fulfillment) {
                if ($status === 'delivered' && $fulfillment->biteship_status === 'delivered') {
                    $transaction = $this->completionService->completeFulfillmentFromBiteship($fulfillment);
                } else {
                    $transaction = $fulfillment->transaction()->firstOrFail();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Webhook diterima.',
                    'order_id' => $orderId,
                    'status' => $fulfillment->fresh()->biteship_status,
                    'fulfillment_status' => $fulfillment->fresh()->status,
                    'transaction_status' => $transaction->fresh()->status,
                ]);
            }

            // Legacy fallback for orders created before fulfillment groups existed.
            $transaction = DB::transaction(function () use ($orderId, $status, $payload) {
                $row = Transaction::query()->where('biteship_order_id', $orderId)->lockForUpdate()->first();
                if (! $row) return null;
                if ($this->shouldIgnoreStatus($row->biteship_status, $status)) return $row->fresh();

                $updates = ['biteship_status' => $status];
                foreach ([
                    'courier_tracking_id' => 'biteship_tracking_id',
                    'courier_waybill_id' => 'biteship_waybill_id',
                    'courier_company' => 'courier',
                ] as $source => $target) {
                    if (isset($payload[$source]) && is_string($payload[$source]) && trim($payload[$source]) !== '') {
                        $updates[$target] = trim($payload[$source]);
                    }
                }
                if (empty($row->tracking_number) && ! empty($updates['biteship_waybill_id'])) {
                    $updates['tracking_number'] = $updates['biteship_waybill_id'];
                }
                $row->update($updates);
                return $row->fresh();
            });

            if (! $transaction) {
                return response()->json(['success' => false, 'message' => 'Biteship order ID tidak ditemukan.'], 404);
            }

            if ($status === 'delivered' && $transaction->biteship_status === 'delivered') {
                $transaction = $this->completionService->completeFromBiteship($transaction);
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
            return response()->json(['success' => false, 'message' => 'Webhook belum dapat diproses.'], 503);
        }
    }

    private function isSupportedStatus(string $status): bool
    {
        return isset(self::PROGRESS_STATUSES[$status])
            || in_array($status, ['on_hold', 'delivered', 'return_in_transit'], true)
            || in_array($status, self::TERMINAL_STATUSES, true);
    }

    private function shouldIgnoreStatus(?string $currentStatus, string $incomingStatus): bool
    {
        if (! $currentStatus || $currentStatus === $incomingStatus) return false;
        if ($currentStatus === 'delivered' || $currentStatus === 'returned') return true;
        if (in_array($currentStatus, self::TERMINAL_STATUSES, true)) return true;
        if ($currentStatus === 'return_in_transit') return $incomingStatus !== 'returned';
        if ($incomingStatus === 'return_in_transit') return false;

        $currentRank = self::PROGRESS_STATUSES[$currentStatus] ?? null;
        $incomingRank = self::PROGRESS_STATUSES[$incomingStatus] ?? null;
        if ($currentRank !== null && $incomingRank !== null) return $incomingRank < $currentRank;
        if ($currentStatus === 'on_hold' || $incomingStatus === 'on_hold') return false;
        return false;
    }
}
