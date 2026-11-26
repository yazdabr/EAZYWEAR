<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DokuMinimarketService
{
    protected string $baseUrl;
    protected string $endpoint;
    protected ?string $clientId;
    protected ?string $secretKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('doku-minimarket.base_url'),
            '/'
        );

        $this->endpoint = config(
            'doku-minimarket.alfamart.endpoint'
        );

        $this->clientId = config(
            'doku-minimarket.client_id'
        );

        $this->secretKey = config(
            'doku-minimarket.secret_key'
        );
    }

    public function isConfigured(): bool
    {
        return filled($this->baseUrl)
            && filled($this->endpoint)
            && filled($this->clientId)
            && filled($this->secretKey);
    }

    /**
     * Generate Alfamart DOKU Generated Payment Code.
     */
    public function createAlfamartPaymentCode(
        string $invoiceNumber,
        int|float|string $amount,
        string $customerName,
        string $requestId,
        ?string $customerEmail = null,
        ?string $info = null
    ): array {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'Konfigurasi DOKU Alfamart belum lengkap.'
            );
        }

        if (
            blank($requestId)
            || strlen($requestId) > 100
        ) {
            throw new RuntimeException(
                'Request-Id Alfamart wajib diisi dan maksimal 100 karakter.'
            );
        }

        if (blank($invoiceNumber) || strlen($invoiceNumber) > 64) {
            throw new RuntimeException(
                'Invoice number harus diisi dan maksimal 64 karakter.'
            );
        }

        $amountString = trim((string) $amount);

        if (! preg_match('/^\d+(?:\.(\d+))?$/', $amountString, $matches)) {
            throw new RuntimeException(
                'Nominal pembayaran tidak valid.'
            );
        }

        if (
            isset($matches[1])
            && trim($matches[1], '0') !== ''
        ) {
            throw new RuntimeException(
                'Nominal Alfamart harus berupa bilangan bulat IDR maksimal 12 digit.'
            );
        }

        $amountDigits = explode('.', $amountString, 2)[0];
        $normalizedAmount = ltrim($amountDigits, '0') ?: '0';

        if (
            $normalizedAmount === '0'
            || strlen($normalizedAmount) > 12
        ) {
            throw new RuntimeException(
                'Nominal Alfamart harus berupa bilangan bulat IDR maksimal 12 digit.'
            );
        }

        $integerAmount = (int) $normalizedAmount;

        if (blank($customerName) || mb_strlen($customerName) > 64) {
            throw new RuntimeException(
                'Nama pelanggan wajib diisi dan maksimal 64 karakter.'
            );
        }

        if (
            filled($customerEmail)
            && mb_strlen($customerEmail) > 128
        ) {
            throw new RuntimeException(
                'Email pelanggan maksimal 128 karakter.'
            );
        }

        if (filled($info) && mb_strlen($info) > 30) {
            throw new RuntimeException(
                'Informasi tambahan maksimal 30 karakter.'
            );
        }

        $payload = [
            'order' => [
                'invoice_number' => $invoiceNumber,
                'amount' => $integerAmount,
            ],

            'online_to_offline_info' => [
                'expired_time' => (int) config(
                    'doku-minimarket.alfamart.expired_time',
                    1440
                ),

                'reusable_status' => false,
            ],

            'customer' => [
                'name' => $customerName,
            ],
        ];

        $notificationUrl = config(
            'doku-minimarket.alfamart.notification_url'
        );

        if (filled($notificationUrl)) {
            $payload['additional_info'] = [
                'override_notification_url' => $notificationUrl,
            ];
        }

        if (filled($customerEmail)) {
            $payload['customer']['email'] = $customerEmail;
        }

        if (filled($info)) {
            $payload['online_to_offline_info']['info'] = $info;
        }

        $requestBody = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_THROW_ON_ERROR
        );

        $timestamp = now('UTC')->format(
            'Y-m-d\TH:i:s\Z'
        );

        $digest = base64_encode(
            hash('sha256', $requestBody, true)
        );

        $signature = $this->generateSignature(
            $requestId,
            $timestamp,
            $this->endpoint,
            $digest
        );

        Log::info('DOKU ALFAMART CREATE REQUEST', [
            'invoice' => $invoiceNumber,
            'request_id' => $requestId,
            'endpoint' => $this->endpoint,
            'amount' => $payload['order']['amount'],
        ]);

        $response = Http::timeout(30)
            ->acceptJson()
            ->withHeaders([
                'Client-Id' => $this->clientId,
                'Request-Id' => $requestId,
                'Request-Timestamp' => $timestamp,
                'Signature' => $signature,
                'Content-Type' => 'application/json',
            ])
            ->withBody($requestBody, 'application/json')
            ->post($this->baseUrl . $this->endpoint);

        Log::info('DOKU ALFAMART CREATE RESPONSE', [
            'invoice' => $invoiceNumber,
            'request_id' => $requestId,
            'http_status' => $response->status(),
        ]);

        $response->throw();

        // Verify response signature before trusting response data.
        $this->verifyResponseSignature(
            $response->body(),
            $response->headers(),
            $requestId
        );

        $responseData = $response->json();

        if (! is_array($responseData)) {
            throw new RuntimeException(
                'Response DOKU Alfamart tidak valid.'
            );
        }

        $responseInvoice = data_get(
            $responseData,
            'order.invoice_number'
        );

        $paymentCode = data_get(
            $responseData,
            'online_to_offline_info.payment_code'
        );

        if ($responseInvoice !== $invoiceNumber) {
            throw new RuntimeException(
                'Invoice pada response DOKU tidak sesuai.'
            );
        }

        if (blank($paymentCode)) {
            throw new RuntimeException(
                'Payment code Alfamart tidak ditemukan pada response DOKU.'
            );
        }

        if (! is_string($paymentCode) || ! ctype_digit($paymentCode)) {
            throw new RuntimeException(
                'Payment code Alfamart dari response DOKU tidak valid.'
            );
        }

        return [
            ...$responseData,
            '_request_id' => $requestId,
        ];
    }

    /**
     * Generate Non-SNAP HMAC-SHA256 request signature.
     */
    protected function generateSignature(
        string $requestId,
        string $timestamp,
        string $requestTarget,
        string $digest
    ): string {
        $component = implode("\n", [
            'Client-Id:' . $this->clientId,
            'Request-Id:' . $requestId,
            'Request-Timestamp:' . $timestamp,
            'Request-Target:' . $requestTarget,
            'Digest:' . $digest,
        ]);

        $hash = hash_hmac(
            'sha256',
            $component,
            $this->secretKey,
            true
        );

        return 'HMACSHA256=' . base64_encode($hash);
    }

    /**
     * Verify DOKU Non-SNAP response signature.
     */
    protected function verifyResponseSignature(
        string $responseBody,
        array $headers,
        string $requestId
    ): void {
        $headerValue = static function (
            array $headers,
            string $name
        ): ?string {
            foreach ($headers as $key => $values) {
                if (strcasecmp($key, $name) === 0) {
                    return is_array($values)
                        ? ($values[0] ?? null)
                        : $values;
                }
            }

            return null;
        };

        $responseClientId = $headerValue(
            $headers,
            'Client-Id'
        );

        $responseRequestId = $headerValue(
            $headers,
            'Request-Id'
        );

        $responseTimestamp = $headerValue(
            $headers,
            'Response-Timestamp'
        );

        $responseSignature = $headerValue(
            $headers,
            'Signature'
        );

        if (
            blank($responseClientId)
            || blank($responseRequestId)
            || blank($responseTimestamp)
            || blank($responseSignature)
        ) {
            throw new RuntimeException(
                'Header signature response DOKU tidak lengkap.'
            );
        }

        if (
            ! hash_equals($this->clientId, $responseClientId)
            || ! hash_equals($requestId, $responseRequestId)
        ) {
            throw new RuntimeException(
                'Identitas response DOKU tidak sesuai dengan request.'
            );
        }

        $digest = base64_encode(
            hash('sha256', $responseBody, true)
        );

        $component = implode("\n", [
            'Client-Id:' . $responseClientId,
            'Request-Id:' . $responseRequestId,
            'Response-Timestamp:' . $responseTimestamp,
            'Request-Target:' . $this->endpoint,
            'Digest:' . $digest,
        ]);

        $expectedSignature = 'HMACSHA256=' . base64_encode(
            hash_hmac(
                'sha256',
                $component,
                $this->secretKey,
                true
            )
        );

        if (! hash_equals($expectedSignature, $responseSignature)) {
            throw new RuntimeException(
                'Signature response DOKU tidak valid.'
            );
        }
    }
}
