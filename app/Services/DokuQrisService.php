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

        $signatureBase64 = base64_encode($signature);

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