<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class DokuQrisService
{
    protected string $baseUrl;
    protected ?string $clientId;
    protected ?string $clientSecret;
    protected string $channelId;
    protected string $privateKeyPath;
    protected string $generateEndpoint;
    protected string $queryEndpoint;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('doku-qris.base_url'), '/');
        $this->clientId = config('doku-qris.client_id');
        $this->clientSecret = config('doku-qris.client_secret');
        $this->channelId = config('doku-qris.channel_id', 'H2H');

        $this->privateKeyPath = base_path(
            config(
                'doku.merchant_private_key_path',
                'storage/app/private/doku/merchant-private.pem'
            )
        );

        $this->generateEndpoint = config(
            'doku-qris.generate_endpoint',
            '/snap-adapter/b2b/v1.0/qr/qr-mpm-generate'
        );

        $this->queryEndpoint = config(
            'doku-qris.query_endpoint',
            '/snap-adapter/b2b/v1.0/qr/qr-mpm-query'
        );
    }

    public function isConfigured(): bool
    {
        return filled($this->baseUrl)
            && filled($this->clientId)
            && filled($this->clientSecret);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getGenerateEndpoint(): string
    {
        return $this->generateEndpoint;
    }

    public function getQueryEndpoint(): string
    {
        return $this->queryEndpoint;
    }

    public function getAccessToken(): array
    {
        if (empty($this->clientId)) {
            throw new RuntimeException(
                'DOKU QRIS Client ID belum dikonfigurasi.'
            );
        }

        if (! file_exists($this->privateKeyPath)) {
            throw new RuntimeException(
                'DOKU merchant private key tidak ditemukan.'
            );
        }

        $privateKey = file_get_contents($this->privateKeyPath);

        if ($privateKey === false) {
            throw new RuntimeException(
                'Gagal membaca merchant private key.'
            );
        }

        $timestamp = now('UTC')->format('Y-m-d\TH:i:s\Z');

        $stringToSign = $this->clientId . '|' . $timestamp;

        $signatureBase64 = $this->createB2bTokenSignature(
            $stringToSign,
            $privateKey
        );

        $endpoint = '/authorization/v1/access-token/b2b';

        $response = Http::timeout(30)
            ->acceptJson()
            ->withHeaders([
                'X-SIGNATURE' => $signatureBase64,
                'X-TIMESTAMP' => $timestamp,
                'X-CLIENT-KEY' => $this->clientId,
                'Content-Type' => 'application/json',
            ])
            ->post(
                $this->baseUrl . $endpoint,
                [
                    'grantType' => 'client_credentials',
                ]
            );

        $response->throw();

        $responseData = $response->json();

        if (! is_array($responseData)) {
            throw new RuntimeException(
                'Response Get Token B2B QRIS tidak valid.'
            );
        }

        return $responseData;
    }

    public function generateQr(
        string $partnerReferenceNo,
        float|int|string $amount,
        ?string $validityPeriod = null
    ): array {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'DOKU QRIS belum dikonfigurasi.'
            );
        }

        $merchantId = config('doku-qris.merchant_id');
        $terminalId = config('doku-qris.terminal_id');
        $postalCode = config('doku-qris.postal_code');
        $feeType = config('doku-qris.fee_type', '1');

        if (blank($merchantId)) {
            throw new RuntimeException(
                'DOKU QRIS Merchant ID belum dikonfigurasi.'
            );
        }

        if (blank($terminalId)) {
            throw new RuntimeException(
                'DOKU QRIS Terminal ID belum dikonfigurasi.'
            );
        }

        if (blank($postalCode)) {
            throw new RuntimeException(
                'DOKU QRIS Postal Code belum dikonfigurasi.'
            );
        }

        $tokenResponse = $this->getAccessToken();

        $accessToken = $tokenResponse['accessToken']
            ?? $tokenResponse['access_token']
            ?? null;

        if (blank($accessToken)) {
            throw new RuntimeException(
                'Access token DOKU QRIS tidak ditemukan.'
            );
        }

        $timestamp = now()->format('Y-m-d\TH:i:sP');

        $payload = [
            'partnerReferenceNo' => $partnerReferenceNo,
            'amount' => [
                'value' => number_format(
                    (float) $amount,
                    2,
                    '.',
                    ''
                ),
                'currency' => 'IDR',
            ],
            'merchantId' => $merchantId,
            'terminalId' => $terminalId,
            'additionalInfo' => [
                'postalCode' => $postalCode,
                'feeType' => $feeType,
            ],
        ];

        if (filled($validityPeriod)) {
            $payload['validityPeriod'] = $validityPeriod;
        }

        $requestBody = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
        );

        if ($requestBody === false) {
            throw new RuntimeException(
                'Gagal membuat request body DOKU QRIS.'
            );
        }

        $signature = $this->generateSymmetricSignature(
            'POST',
            $this->generateEndpoint,
            $accessToken,
            $requestBody,
            $timestamp
        );

        $externalId = $this->generateExternalId();

        $response = Http::timeout(30)
            ->acceptJson()
            ->withHeaders([
                'X-PARTNER-ID' => $this->clientId,
                'X-EXTERNAL-ID' => $externalId,
                'X-TIMESTAMP' => $timestamp,
                'X-SIGNATURE' => $signature,
                'Authorization' => 'Bearer ' . $accessToken,
                'CHANNEL-ID' => $this->channelId,
                'Content-Type' => 'application/json',
            ])
            ->withBody($requestBody, 'application/json')
            ->post(
                $this->baseUrl . $this->generateEndpoint
            );

        $response->throw();

        $responseData = $response->json();

        if (! is_array($responseData)) {
            throw new RuntimeException(
                'Response Generate QRIS tidak valid.'
            );
        }

        return [
            ...$responseData,
            '_external_id' => $externalId,
        ];
    }

    public function queryQr(
        string $originalReferenceNo,
        string $originalPartnerReferenceNo
    ): array {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'DOKU QRIS belum dikonfigurasi.'
            );
        }

        $merchantId = config('doku-qris.merchant_id');

        if (blank($merchantId)) {
            throw new RuntimeException(
                'DOKU QRIS Merchant ID belum dikonfigurasi.'
            );
        }

        $tokenResponse = $this->getAccessToken();

        $accessToken = $tokenResponse['accessToken']
            ?? $tokenResponse['access_token']
            ?? null;

        if (blank($accessToken)) {
            throw new RuntimeException(
                'Access token DOKU QRIS tidak ditemukan.'
            );
        }

        $timestamp = now()->format('Y-m-d\TH:i:sP');

        $payload = [
            'originalReferenceNo' => $originalReferenceNo,
            'originalPartnerReferenceNo' => $originalPartnerReferenceNo,
            'merchantId' => $merchantId,
            'serviceCode' => '47',
        ];

        $requestBody = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
        );

        if ($requestBody === false) {
            throw new RuntimeException(
                'Gagal membuat request body Query QRIS.'
            );
        }

        $signature = $this->generateSymmetricSignature(
            'POST',
            $this->queryEndpoint,
            $accessToken,
            $requestBody,
            $timestamp
        );

        $externalId = $this->generateExternalId();

        $response = Http::timeout(30)
            ->acceptJson()
            ->withHeaders([
                'X-PARTNER-ID' => $this->clientId,
                'X-EXTERNAL-ID' => $externalId,
                'X-TIMESTAMP' => $timestamp,
                'X-SIGNATURE' => $signature,
                'Authorization' => 'Bearer ' . $accessToken,
                'CHANNEL-ID' => $this->channelId,
                'Content-Type' => 'application/json',
            ])
            ->withBody($requestBody, 'application/json')
            ->post(
                $this->baseUrl . $this->queryEndpoint
            );

        $response->throw();

        $responseData = $response->json();

        if (! is_array($responseData)) {
            throw new RuntimeException(
                'Response Query QRIS tidak valid.'
            );
        }

        return [
            ...$responseData,
            '_external_id' => $externalId,
        ];
    }

    protected function createB2bTokenSignature(
        string $stringToSign,
        string $privateKey
    ): string {
        $signature = '';

        $signed = openssl_sign(
            $stringToSign,
            $signature,
            $privateKey,
            OPENSSL_ALGO_SHA256
        );

        if (! $signed) {
            throw new RuntimeException(
                'Gagal membuat signature Get Token B2B QRIS.'
            );
        }

        return base64_encode($signature);
    }

    public function generateSymmetricSignature(
        string $httpMethod,
        string $endpoint,
        string $accessToken,
        string $requestBody,
        string $timestamp
    ): string {
        if (empty($this->clientSecret)) {
            throw new RuntimeException(
                'DOKU QRIS Client Secret belum dikonfigurasi.'
            );
        }

        $bodyHash = strtolower(
            hash('sha256', $requestBody)
        );

        $stringToSign =
            strtoupper($httpMethod)
            . ':'
            . $endpoint
            . ':'
            . $accessToken
            . ':'
            . $bodyHash
            . ':'
            . $timestamp;

        $signature = hash_hmac(
            'sha512',
            $stringToSign,
            $this->clientSecret,
            true
        );

        return base64_encode($signature);
    }

    public function generateExternalId(): string
    {
        return now('Asia/Makassar')->format('YmdHis')
            . random_int(1000, 9999);
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }
}