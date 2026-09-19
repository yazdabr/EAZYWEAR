<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class DokuService
{
    protected string $baseUrl;

    protected ?string $clientId;

    protected ?string $apiKey;

    protected ?string $secretKey;

    protected string $privateKeyPath;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('doku.base_url'),
            '/'
        );

        $this->clientId = config('doku.client_id');

        $this->apiKey = config('doku.api_key');

        $this->secretKey = config('doku.secret_key');

        $this->privateKeyPath = base_path(
            config('doku.merchant_private_key_path')
        );
    }

    /**
     * Memeriksa konfigurasi dasar DOKU.
     */
    public function isConfigured(): bool
    {
        return !empty($this->baseUrl)
            && !empty($this->clientId)
            && !empty($this->apiKey)
            && !empty($this->secretKey);
    }

    /**
     * Mengambil base URL DOKU.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Mengambil B2B Access Token DOKU.
     */
    public function getAccessToken(): array
    {
        if (empty($this->clientId)) {
            throw new RuntimeException(
                'DOKU Client ID belum dikonfigurasi.'
            );
        }

        if (!file_exists($this->privateKeyPath)) {
            throw new RuntimeException(
                'DOKU merchant private key tidak ditemukan.'
            );
        }

        $privateKey = file_get_contents(
            $this->privateKeyPath
        );

        if ($privateKey === false) {
            throw new RuntimeException(
                'Gagal membaca merchant private key.'
            );
        }

        /**
         * Dokumentasi DOKU:
         *
         * stringToSign =
         * client_ID + "|" + X-TIMESTAMP
         *
         * Timestamp menggunakan UTC+0.
         */
        $timestamp = now('UTC')->format(
            'Y-m-d\TH:i:s\Z'
        );

        $stringToSign = $this->clientId
            . '|'
            . $timestamp;

        /**
         * Signature:
         * SHA256withRSA menggunakan private key.
         */
        $signature = '';

        $signed = openssl_sign(
            $stringToSign,
            $signature,
            $privateKey,
            OPENSSL_ALGO_SHA256
        );

        if (!$signed) {
            throw new RuntimeException(
                'Gagal membuat signature Get Token B2B.'
            );
        }

        $signatureBase64 = base64_encode(
            $signature
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

        return $response->json();
    }

    /**
     * Membuat signature symmetric HMAC-SHA512 untuk request DOKU.
     */
    private function generateSymmetricSignature(
        string $httpMethod,
        string $endpoint,
        string $accessToken,
        string $requestBody,
        string $timestamp
    ): string {
        if (empty($this->secretKey)) {
            throw new RuntimeException(
                'DOKU Secret Key belum dikonfigurasi.'
            );
        }

        $bodyHash = strtolower(
            hash('sha256', $requestBody)
        );

        $stringToSign = strtoupper($httpMethod)
            . ':'
            . $endpoint
            . ':'
            . $accessToken
            . ':'
            . $bodyHash
            . ':'
            . $timestamp;

        return hash_hmac(
            'sha512',
            $stringToSign,
            $this->secretKey
        );
    }

    public function createVirtualAccount(array $data): array
    {
        $tokenResponse = $this->getAccessToken();

        $accessToken = $tokenResponse['accessToken'] ?? null;

        if (empty($accessToken)) {
            throw new RuntimeException(
                'Access token DOKU tidak ditemukan dalam response.'
            );
        }

        $endpoint = config('doku.va.endpoint');

        $timestamp = now('Asia/Jakarta')->format('Y-m-d\TH:i:sP');

        $externalId = now('Asia/Jakarta')->format('YmdHis')
            . random_int(1000, 9999);

        $body = [
            'partnerServiceId' => $data['partnerServiceId'] ?? '',
            'customerNo' => $data['customerNo'] ?? '',
            'virtualAccountNo' => $data['virtualAccountNo'] ?? '',
            'virtualAccountName' => $data['virtualAccountName'] ?? '',
            'virtualAccountEmail' => $data['virtualAccountEmail'] ?? '',
            'virtualAccountPhone' => $data['virtualAccountPhone'] ?? '',
            'trxId' => $data['trxId'] ?? '',
            'totalAmount' => [
                'value' => $data['amount'] ?? '0.00',
                'currency' => 'IDR',
            ],
            'additionalInfo' => [
                'channel' => $data['channel'] ?? 'VIRTUAL_ACCOUNT_BCA',
            ],
            'virtualAccountTrxType' => config(
                'doku.va.virtual_account_trx_type',
                'C'
            ),
            'expiredDate' => $data['expiredDate'] ?? now('Asia/Jakarta')
            ->addHours(24)
            ->format('Y-m-d\TH:i:sP'),
        ];

        $requestBody = json_encode(
            $body,
            JSON_UNESCAPED_SLASHES
        );

        if ($requestBody === false) {
            throw new RuntimeException(
                'Gagal membuat JSON request body DOKU.'
            );
        }

        $signature = $this->generateSymmetricSignature(
            'POST',
            $endpoint,
            $accessToken,
            $requestBody,
            $timestamp
        );

        $response = Http::timeout(30)
            ->acceptJson()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'X-SIGNATURE' => $signature,
                'X-TIMESTAMP' => $timestamp,
                'X-PARTNER-ID' => $this->clientId,
                'X-EXTERNAL-ID' => $externalId,
                'CHANNEL-ID' => config('doku.va.channel_id', 'H2H'),
                'Content-Type' => 'application/json',
            ])
            ->withBody($requestBody, 'application/json')
            ->post($this->baseUrl . $endpoint);

        $response->throw();

        return $response->json();
    }
}