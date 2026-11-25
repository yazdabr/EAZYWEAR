<?php

namespace App\\Services;

use Illuminate\\Support\\Facades\\Http;
use Illuminate\\Support\\Facades\\Log;
use Illuminate\\Support\\Str;
use RuntimeException;

class DokuO2oService
{
    public function __construct(
        private readonly ?string $clientId = null,
        private readonly ?string $secretKey = null,
    ) {
    }

    private function client(): string
    {
        return (string) ($this->clientId ?: config('doku.client_id'));
    }

    private function secret(): string
    {
        return (string) ($this->secretKey ?: config('doku.secret_key'));
    }

    public function isConfigured(): bool
    {
        return filled($this->client()) && filled($this->secret())
            && filled(config('doku.base_url'));
    }

    public function generatePaymentCode(string $method, array $data): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Konfigurasi DOKU O2O belum lengkap.');
        }

        $method = strtoupper($method);
        $endpoint = match ($method) {
            'ALFAMART' => '/alfa-online-to-offline/v2/payment-code',
            'INDOMARET' => '/indomaret-online-to-offline/v2/payment-code',
            default => throw new RuntimeException('Metode pembayaran O2O tidak dikenal.'),
        };

        $invoice = (string) ($data['invoice_number'] ?? '');
        $amount = (int) round((float) ($data['amount'] ?? 0));
        $name = trim((string) ($data['customer_name'] ?? ''));
        $email = trim((string) ($data['customer_email'] ?? ''));

        if ($invoice === '' || $amount <= 0 || $name === '') {
            throw new RuntimeException('Data pembuatan payment code DOKU tidak lengkap.');
        }

        $payload = [
            'order' => [
                'invoice_number' => $invoice,
                'amount' => $amount,
            ],
            'online_to_offline_info' => [
                'expired_time' => (int) config('doku.o2o.expired_time', 1440),
                'reusable_status' => false,
                'info' => (string) config('doku.o2o.info', 'Eazywear Indonesia'),
            ],
            'customer' => [
                'name' => mb_substr($name, 0, 64),
            ],
        ];

        if ($email !== '') {
            $payload['customer']['email'] = mb_substr($email, 0, 128);
        }

        if ($method === 'INDOMARET') {
            $payload['indomaret_info'] = [
                'receipt' => [
                    'description' => (string) config('doku.o2o.indomaret_receipt_description', 'Pembelian Produk Eazywear'),
                ],
            ];
        }

        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($body === false) {
            throw new RuntimeException('Gagal membuat request body DOKU O2O.');
        }

        $requestId = (string) Str::uuid();
        $timestamp = now('UTC')->format('Y-m-d\\TH:i:s\\Z');
        $digest = base64_encode(hash('sha256', $body, true));
        $canonical = implode("\\n", [
            'Client-Id:' . $this->client(),
            'Request-Id:' . $requestId,
            'Request-Timestamp:' . $timestamp,
            'Request-Target:' . $endpoint,
            'Digest:' . $digest,
        ]);
        $signature = 'HMACSHA256=' . base64_encode(
            hash_hmac('sha256', $canonical, $this->secret(), true)
        );

        $response = Http::timeout(30)
            ->acceptJson()
            ->withHeaders([
                'Client-Id' => $this->client(),
                'Request-Id' => $requestId,
                'Request-Timestamp' => $timestamp,
                'Signature' => $signature,
                'Content-Type' => 'application/json',
            ])
            ->withBody($body, 'application/json')
            ->post(rtrim(config('doku.base_url'), '/') . $endpoint);

        $response->throw();
        $result = $response->json();

        if (! is_array($result)) {
            throw new RuntimeException('Respons payment code DOKU tidak valid.');
        }

        $paymentCode = data_get($result, 'online_to_offline_info.payment_code');
        if (! filled($paymentCode)) {
            throw new RuntimeException('DOKU tidak mengembalikan payment code.');
        }

        Log::info('DOKU O2O PAYMENT CODE CREATED', [
            'invoice' => $invoice,
            'method' => $method,
            'request_id' => $requestId,
            'expired_date' => data_get($result, 'online_to_offline_info.expired_date'),
        ]);

        return [
            'request_id' => $requestId,
            'payment_code' => (string) $paymentCode,
            'expired_date' => data_get($result, 'online_to_offline_info.expired_date'),
            'expired_date_utc' => data_get($result, 'online_to_offline_info.expired_date_utc'),
            'how_to_pay_page' => data_get($result, 'online_to_offline_info.how_to_pay_page'),
            'raw' => $result,
        ];
    }

    public function verifyNotification(string $rawBody, string $requestId, string $timestamp, string $signature, string $requestTarget): bool
    {
        if (! $this->isConfigured() || blank($requestId) || blank($timestamp) || blank($signature)) {
            return false;
        }

        $digest = base64_encode(hash('sha256', $rawBody, true));
        $canonical = implode("\\n", [
            'Client-Id:' . $this->client(),
            'Request-Id:' . $requestId,
            'Request-Timestamp:' . $timestamp,
            'Request-Target:' . $requestTarget,
            'Digest:' . $digest,
        ]);
        $expected = 'HMACSHA256=' . base64_encode(
            hash_hmac('sha256', $canonical, $this->secret(), true)
        );

        return hash_equals($expected, trim($signature));
    }
}
