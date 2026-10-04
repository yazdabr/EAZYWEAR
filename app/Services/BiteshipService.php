<?php

namespace App\Services;

use App\Exceptions\BiteshipApiException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class BiteshipService
{
    protected string $baseUrl;
    protected ?string $apiKey;
    protected int $timeout;
    protected array $origin;
    protected ?string $couriers;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('biteship.base_url'), '/');
        $this->apiKey = config('biteship.api_key');
        $this->timeout = (int) config('biteship.timeout', 30);
        $this->origin = (array) config('biteship.origin', []);
        $this->couriers = config('biteship.couriers');
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== ''
            && ! empty($this->apiKey);
    }

    /**
     * Retrieve courier rates from Biteship.
     *
     * This method only handles the Biteship API integration.
     * It does not create/update transactions, calculate the final order
     * total, mutate stock, or persist any database record.
     */
    public function getCourierRates(array $data): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Konfigurasi Biteship belum lengkap.');
        }

        $payload = $this->buildRatesPayload($data);

        $response = $this->request(
            'POST',
            '/v1/rates/couriers',
            $payload
        );

        return [
            'success' => (bool) ($response['success'] ?? false),
            'rates' => $this->normalizeRates($response),
            'raw' => $response,
        ];
    }

    protected function buildRatesPayload(array $data): array
    {
        $destinationLatitude = $data['destination_latitude'] ?? null;
        $destinationLongitude = $data['destination_longitude'] ?? null;
        $destinationPostalCode = $data['destination_postal_code'] ?? null;
        $couriers = $this->couriers;
        $items = $data['items'] ?? [];

        if (
            ($destinationLatitude === null || $destinationLongitude === null)
            && empty($destinationPostalCode)
        ) {
            throw new RuntimeException(
                'Tujuan pengiriman membutuhkan koordinat atau kode pos.'
            );
        }

        if (empty($couriers)) {
            throw new RuntimeException(
                'Courier Biteship belum dikonfigurasi.'
            );
        }

        if ($items === []) {
            throw new RuntimeException(
                'Item pengiriman tidak boleh kosong.'
            );
        }

        $payload = [
            'couriers' => $couriers,
            'items' => $this->normalizeItems($items),
        ];

        $originLatitude = $data['origin_latitude']
            ?? $this->origin['latitude']
            ?? null;

        $originLongitude = $data['origin_longitude']
            ?? $this->origin['longitude']
            ?? null;

        $originPostalCode = $data['origin_postal_code']
            ?? $this->origin['postal_code']
            ?? null;

        if (
            $originLatitude !== null
            && $originLongitude !== null
        ) {
            $payload['origin_latitude'] = (float) $originLatitude;
            $payload['origin_longitude'] = (float) $originLongitude;
        } elseif (! empty($originPostalCode)) {
            $payload['origin_postal_code'] = (int) $originPostalCode;
        } else {
            throw new RuntimeException(
                'Origin pengiriman membutuhkan koordinat atau kode pos.'
            );
        }

        if (
            $destinationLatitude !== null
            && $destinationLongitude !== null
        ) {
            $payload['destination_latitude'] =
                (float) $destinationLatitude;

            $payload['destination_longitude'] =
                (float) $destinationLongitude;
        } else {
            $payload['destination_postal_code'] =
                (int) $destinationPostalCode;
        }

        return $payload;
    }

    protected function normalizeItems(array $items): array
    {
        return array_map(function (array $item): array {
            if (
                empty($item['name'])
                || ! array_key_exists('value', $item)
                || ! array_key_exists('quantity', $item)
                || ! array_key_exists('weight', $item)
            ) {
                throw new RuntimeException(
                    'Setiap item Biteship wajib memiliki name, value, quantity, dan weight.'
                );
            }

            if ((int) $item['weight'] <= 0) {
                throw new RuntimeException(
                    'Berat item Biteship harus lebih besar dari 0 gram.'
                );
            }

            return [
                'name' => (string) $item['name'],
                'description' => isset($item['description'])
                    ? (string) $item['description']
                    : null,
                'value' => (int) $item['value'],
                'quantity' => (int) $item['quantity'],
                'weight' => (int) $item['weight'],
            ];
        }, $items);
    }

    protected function normalizeRates(array $response): array
    {
        $pricing = $response['pricing'] ?? [];

        if (! is_array($pricing)) {
            return [];
        }

        return array_values(array_map(
            static function (array $rate): array {
                return [
                    'courier_code' => $rate['courier_code']
                        ?? $rate['company']
                        ?? null,

                    'courier_name' => $rate['courier_name'] ?? null,

                    'service_code' => $rate['courier_service_code']
                        ?? null,

                    'service_name' => $rate['courier_service_name']
                        ?? null,

                    'price' => isset($rate['price'])
                        ? (int) $rate['price']
                        : null,

                    'duration' => $rate['duration'] ?? null,

                    'service_type' => $rate['service_type'] ?? null,

                    'shipping_type' => $rate['shipping_type'] ?? null,
                ];
            },
            $pricing
        ));
    }

    protected function request(
        string $method,
        string $endpoint,
        array $payload = []
    ): array {
        $response = Http::timeout($this->timeout)
            ->acceptJson()
            ->withHeaders([
                'Authorization' => (string) $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->send(
                strtoupper($method),
                $this->baseUrl . $endpoint,
                ['json' => $payload]
            );

        if ($response->failed()) {
            $body = $response->json();

            if (! is_array($body)) {
                $body = [];
            }

            \Log::error('BITESHIP API REQUEST FAILED', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'code' => $body['code'] ?? null,
                'message' => $body['message']
                    ?? $body['error']
                    ?? null,
            ]);

            $message = $body['message']
                ?? $body['error']
                ?? 'Permintaan ke Biteship gagal.';

            throw new BiteshipApiException(
                'Biteship gagal: ' . $message,
                $response->status(),
                $body,
            );
        }

        $body = $response->json();

        return is_array($body)
            ? $body
            : [];
    }

    public function createOrder(array $data): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'Konfigurasi Biteship belum lengkap.'
            );
        }

        $referenceId = (string) $data['reference_id'];

        $payload = [
            'reference_id' => $referenceId,

            'origin_contact_name' =>
                (string) config('biteship.origin.contact_name'),

            'origin_contact_phone' =>
                (string) config('biteship.origin.contact_phone'),

            'origin_address' =>
                (string) config('biteship.origin.address'),

            'origin_postal_code' =>
                (int) config('biteship.origin.postal_code'),

            'destination_contact_name' =>
                (string) $data['destination_contact_name'],

            'destination_contact_phone' =>
                (string) $data['destination_contact_phone'],

            'destination_address' =>
                (string) $data['destination_address'],

            'destination_postal_code' =>
                (int) $data['destination_postal_code'],

            'courier_company' =>
                (string) $data['courier_company'],

            'courier_type' =>
                (string) $data['courier_type'],

            'delivery_type' =>
                (string) ($data['delivery_type'] ?? 'now'),

            'items' =>
                $this->normalizeItems($data['items']),
        ];

        if (! empty($data['destination_area_id'])) {
            $payload['destination_area_id'] =
                $data['destination_area_id'];
        }

        if (
            ! empty($data['destination_latitude'])
            && ! empty($data['destination_longitude'])
        ) {
            $payload['destination_latitude'] =
                (float) $data['destination_latitude'];

            $payload['destination_longitude'] =
                (float) $data['destination_longitude'];
        }

        try {
            $response = $this->request(
                'POST',
                '/v1/orders',
                $payload
            );
        } catch (BiteshipApiException $e) {
            /*
             * 40002060 = duplicate reference_id.
             *
             * Jangan melakukan blind retry terhadap POST.
             * Gunakan order_id yang diberikan Biteship untuk
             * mengambil existing order.
             */
            if ($e->errorCode() !== 40002060) {
                throw $e;
            }

            $details = $e->body['details'] ?? [];

            $existingOrderId = $details['order_id'] ?? null;
            $existingReferenceId = $details['reference_id'] ?? null;

            if (
                ! is_string($existingOrderId)
                || trim($existingOrderId) === ''
            ) {
                throw new RuntimeException(
                    'Biteship duplicate reference tidak menyertakan order ID.'
                );
            }

            /*
             * Validasi bahwa duplicate reference memang untuk
             * reference_id transaksi yang sedang kita proses.
             */
            if (
                ! is_string($existingReferenceId)
                || trim($existingReferenceId) === ''
                || $existingReferenceId !== $referenceId
            ) {
                throw new RuntimeException(
                    'Biteship duplicate reference memiliki reference ID yang berbeda.'
                );
            }

            $existingOrder = $this->getOrder(
                $existingOrderId
            );

            if (! $existingOrder['success']) {
                throw new RuntimeException(
                    'Biteship duplicate reference ditemukan, tetapi existing order tidak dapat diverifikasi.'
                );
            }

            /*
             * Pastikan GET benar-benar mengembalikan order
             * yang sama dengan order_id dari duplicate response.
             */
            if (
                (string) ($existingOrder['order_id'] ?? '')
                !== $existingOrderId
            ) {
                throw new RuntimeException(
                    'Biteship existing order ID tidak cocok.'
                );
            }

            /*
             * reference_id pada Retrieve Order dapat saja null.
             * Kalau Biteship mengirim nilainya, kita tetap validasi.
             */
            $retrievedReferenceId =
                $existingOrder['reference_id'] ?? null;

            if (
                $retrievedReferenceId !== null
                && (string) $retrievedReferenceId !== $referenceId
            ) {
                throw new RuntimeException(
                    'Biteship existing order memiliki reference ID yang berbeda.'
                );
            }

            \Log::warning(
                'BITESHIP ORDER RECOVERED FROM DUPLICATE REFERENCE',
                [
                    'reference_id' => $referenceId,
                    'order_id' => $existingOrderId,
                ]
            );

            return $existingOrder;
        }

        return [
            'success' => ! empty($response['success']),

            'order_id' => $response['id'] ?? null,

            'tracking_id' =>
                $response['courier']['tracking_id'] ?? null,

            'waybill_id' =>
                $response['courier']['waybill_id'] ?? null,

            'status' =>
                $response['status'] ?? null,

            'reference_id' =>
                $response['reference_id'] ?? $referenceId,

            'courier_code' =>
                $response['courier']['company'] ?? null,

            'courier_type' =>
                $response['courier']['type'] ?? null,

            'raw' => $response,
        ];
    }

    public function getOrder(string $orderId): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'Konfigurasi Biteship belum lengkap.'
            );
        }

        if ($orderId === '') {
            throw new InvalidArgumentException(
                'Biteship order ID wajib diisi.'
            );
        }

        $response = $this->request(
            'GET',
            '/v1/orders/' . rawurlencode($orderId)
        );

        return [
            'success' => ! empty($response['success']),

            'order_id' => $response['id'] ?? null,

            'reference_id' =>
                $response['reference_id'] ?? null,

            'tracking_id' =>
                $response['courier']['tracking_id'] ?? null,

            'waybill_id' =>
                $response['courier']['waybill_id'] ?? null,

            'courier_code' =>
                $response['courier']['company'] ?? null,

            'courier_type' =>
                $response['courier']['type'] ?? null,

            'status' =>
                $response['status'] ?? null,

            'price' =>
                $response['price'] ?? null,

            'raw' => $response,
        ];
    }
}
