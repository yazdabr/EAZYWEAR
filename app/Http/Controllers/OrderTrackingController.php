<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionShipment;
use App\Services\BiteshipService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class OrderTrackingController extends Controller
{
    public function index(): View
    {
        return view('orders.tracking-search');
    }

    public function search(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'invoice_number' => [
                'required',
                'string',
                'max:100',
            ],

            'email' => [
                'required',
                'email',
                'max:150',
            ],
        ]);

        $transaction = Transaction::with([
            'items.productVariant.product',
            'items.productVariant.size',
            'items.productVariant.color',
            'orderStatusHistories',
            'shipments.fulfillments.items.productVariant.product.images',
            'shipments.fulfillments.items.productVariant.size',
            'shipments.fulfillments.items.productVariant.color',
        ])
        ->where('invoice_number', $validated['invoice_number'])
        ->where('shipping_email', $validated['email'])
        ->first();

        if (! $transaction) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Pesanan tidak ditemukan. Pastikan nomor invoice dan email sudah benar.'
                );
        }

        return view(
            'orders.tracking-detail',
            compact('transaction')
        );
    }

    public function trackingShipment(
        Request $request,
        string $invoice,
        TransactionShipment $shipment,
        BiteshipService $biteshipService
    ): JsonResponse {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:150'],
        ]);

        $transaction = Transaction::query()
            ->where('invoice_number', $invoice)
            ->where('shipping_email', $validated['email'])
            ->first();

        if (
            ! $transaction ||
            (int) $shipment->transaction_id !== (int) $transaction->id
        ) {
            abort(404);
        }

        if (! $shipment->biteship_tracking_id) {
            return response()->json([
                'success' => false,
                'message' => 'Data tracking belum tersedia untuk pengiriman ini.',
            ], 422);
        }

        try {
            $tracking = $biteshipService->getTracking(
                $shipment->biteship_tracking_id
            );

            if (empty($tracking['success'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Biteship belum menyediakan data tracking.',
                ], 422);
            }

            // Ambil foto bukti pengiriman melalui GET Order.
            $proofImages = [];

            if ($shipment->biteship_order_id) {
                try {
                    $order = $biteshipService->getOrder(
                        $shipment->biteship_order_id
                    );

                    if (! empty($order['success'])) {
                        $orderHistory = data_get(
                            $order,
                            'raw.courier.history',
                            []
                        );

                        if (is_array($orderHistory)) {
                            foreach ($orderHistory as $historyItem) {
                                $images = $historyItem['proof_of_delivery_images'] ?? [];

                                if (is_string($images)) {
                                    $images = [$images];
                                }

                                if (! is_array($images)) {
                                    continue;
                                }

                                foreach ($images as $image) {
                                    if (
                                        ! is_string($image) ||
                                        ! filter_var($image, FILTER_VALIDATE_URL) ||
                                        parse_url($image, PHP_URL_SCHEME) !== 'https'
                                    ) {
                                        continue;
                                    }

                                    $proofImages[] = $image;
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Kegagalan mengambil foto tidak boleh menggagalkan tracking.
                    report($e);
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tracking_number' => $shipment->tracking_number
                        ?: ($shipment->biteship_waybill_id ?: ($tracking['waybill_id'] ?? null)),
                    'courier' => $shipment->courier
                        ?: ($shipment->courier_code ?: ($tracking['courier_code'] ?? null)),
                    'history' => $tracking['history'] ?? [],
                    'proof_images' => array_values(array_unique($proofImages)),
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Tracking belum dapat dimuat. Silakan coba kembali.',
            ], 502);
        }
    }
}
