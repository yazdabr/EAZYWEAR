<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
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

        $response = $this->request('POST', '/v1/rates/couriers', $payload);

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
            throw new RuntimeException('Courier Biteship belum dikonfigurasi.');
        }

        if ($items === []) {
            throw new RuntimeException('Item pengiriman tidak boleh kosong.');
        }

        $payload = [
            'couriers' => $couriers,
            'items' => $this->normalizeItems($items),
        ];

        $originLatitude = $data['origin_latitude'] ?? $this->origin['latitude'] ?? null;
        $originLongitude = $data['origin_longitude'] ?? $this->origin['longitude'] ?? null;
        $originPostalCode = $data['origin_postal_code'] ?? $this->origin['postal_code'] ?? null;

        if ($originLatitude !== null && $originLongitude !== null) {
            $payload['origin_latitude'] = (float) $originLatitude;
            $payload['origin_longitude'] = (float) $originLongitude;
        } elseif (! empty($originPostalCode)) {
            $payload['origin_postal_code'] = (int) $originPostalCode;
        } else {
            throw new RuntimeException(
                'Origin pengiriman membutuhkan koordinat atau kode pos.'
            );
        }

        if ($destinationLatitude !== null && $destinationLongitude !== null) {
            $payload['destination_latitude'] = (float) $destinationLatitude;
            $payload['destination_longitude'] = (float) $destinationLongitude;
        } else {
            $payload['destination_postal_code'] = (int) $destinationPostalCode;
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
                    'courier_code' => $rate['courier_code'] ?? $rate['company'] ?? null,
                    'courier_name' => $rate['courier_name'] ?? null,
                    'service_code' => $rate['courier_service_code'] ?? null,
                    'service_name' => $rate['courier_service_name'] ?? null,
                    'price' => isset($rate['price']) ? (int) $rate['price'] : null,
                    'duration' => $rate['duration'] ?? null,
                    'service_type' => $rate['service_type'] ?? null,
                    'shipping_type' => $rate['shipping_type'] ?? null,
                ];
            },
            $pricing
        ));
    }

    protected function request(string $method, string $endpoint, array $payload = []): array
    {
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

            \Log::error('BITESHIP API REQUEST FAILED', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'code' => $body['code'] ?? null,
                'message' => $body['message'] ?? null,
            ]);

            $message = $body['message'] ?? 'Permintaan ke Biteship gagal.';

            throw new RuntimeException(
                'Biteship gagal: ' . $message,
                $response->status()
            );
        }

        return $response->json() ?? [];
    }
}
