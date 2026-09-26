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
        $this->baseUrl = rtrim(config('doku.base_url'), '/');
        $this->clientId = config('doku.client_id');
        $this->apiKey = config('doku.api_key');
        $this->secretKey = config('doku.secret_key');
        $this->privateKeyPath = base_path(config('doku.merchant_private_key_path'));
    }

    public function isConfigured(): bool
    {
        return ! empty($this->baseUrl)
            && ! empty($this->clientId)
            && ! empty($this->apiKey)
            && ! empty($this->secretKey);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getAccessToken(): array
    {
        if (empty($this->clientId)) {
            throw new RuntimeException('DOKU Client ID belum dikonfigurasi.');
        }

        if (! file_exists($this->privateKeyPath)) {
            throw new RuntimeException('DOKU merchant private key tidak ditemukan.');
        }

        $privateKey = file_get_contents($this->privateKeyPath);

        if ($privateKey === false) {
            throw new RuntimeException('Gagal membaca merchant private key.');
        }

        $timestamp = now('UTC')->format('Y-m-d\TH:i:s\Z');
        $stringToSign = $this->clientId . '|' . $timestamp;
        $signature = '';
        $signed = openssl_sign($stringToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (! $signed) {
            throw new RuntimeException('Gagal membuat signature Get Token B2B.');
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
            ->post($this->baseUrl . $endpoint, ['grantType' => 'client_credentials']);

        $response->throw();

        return $response->json();
    }

    private function generateSymmetricSignature(
        string $httpMethod,
        string $endpoint,
        string $accessToken,
        string $requestBody,
        string $timestamp
    ): string {
        if (empty($this->secretKey)) {
            throw new RuntimeException('DOKU Secret Key belum dikonfigurasi.');
        }

        $bodyHash = strtolower(hash('sha256', $requestBody));
        $stringToSign = strtoupper($httpMethod) . ':' . $endpoint . ':' . $accessToken . ':' . $bodyHash . ':' . $timestamp;
        $signature = hash_hmac('sha512', $stringToSign, $this->secretKey, true);

        return base64_encode($signature);
    }

    public function createVirtualAccount(array $data): array
    {
        $tokenResponse = $this->getAccessToken();
        $accessToken = $tokenResponse['accessToken'] ?? null;

        if (empty($accessToken)) {
            throw new RuntimeException('Access token DOKU tidak ditemukan dalam response.');
        }

        $endpoint = config('doku.va.endpoint');
        $timestamp = now('Asia/Makassar')->format('Y-m-d\TH:i:sP');
        $externalId = now('Asia/Makassar')->format('YmdHis') . random_int(1000, 9999);
        $partnerServiceId = str_pad((string) ($data['partnerServiceId'] ?? config('doku.va.partner_service_id', '19008')), 8, ' ', STR_PAD_LEFT);
        $customerNo = (string) ($data['customerNo'] ?? '0');
        $virtualAccountNo = $data['virtualAccountNo'] ?? ($partnerServiceId . $customerNo);

        $body = [
            'partnerServiceId' => $partnerServiceId,
            'customerNo' => $customerNo,
            'virtualAccountNo' => $virtualAccountNo,
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
            'virtualAccountTrxType' => config('doku.va.virtual_account_trx_type', 'C'),
            'expiredDate' => $data['expiredDate'] ?? now('Asia/Makassar')->addHours(24)->format('Y-m-d\TH:i:sP'),
        ];

        $requestBody = json_encode($body, JSON_UNESCAPED_SLASHES);

        if ($requestBody === false) {
            throw new RuntimeException('Gagal membuat JSON request body DOKU.');
        }

        $signature = $this->generateSymmetricSignature('POST', $endpoint, $accessToken, $requestBody, $timestamp);

        \Log::info('DOKU CREATE VA REQUEST', [
            'endpoint' => $endpoint,
            'external_id' => $externalId,
            'timestamp' => $timestamp,
            'body' => $body,
        ]);

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

        $responseData = $response->json();

        \Log::info('DOKU CREATE VA RESPONSE', [
            'external_id' => $externalId,
            'response' => $responseData,
        ]);

        if (! is_array($responseData)) {
            throw new RuntimeException('Respons Create VA DOKU tidak valid.');
        }

        $responseData['_external_id'] = $externalId;

        return $responseData;
    }

    public function checkVirtualAccountStatus(array $data): array
    {
        $tokenResponse = $this->getAccessToken();
        $accessToken = $tokenResponse['accessToken'] ?? null;

        if (empty($accessToken)) {
            throw new RuntimeException('Access token DOKU tidak ditemukan dalam response.');
        }

        $endpoint = '/orders/v1.0/transfer-va/status';
        $timestamp = now('Asia/Makassar')->format('Y-m-d\TH:i:sP');
        $externalId = now('Asia/Makassar')->format('YmdHis') . random_int(1000, 9999);
        $partnerServiceId = str_pad(
            (string) (
                $data['partnerServiceId']
                ?? config('doku.va.merchant_bin', '190089')
            ),
            8,
            ' ',
            STR_PAD_LEFT
        );
        $customerNo = (string) ($data['customerNo'] ?? '0');

        $virtualAccountNo = (string) ($data['virtualAccountNo'] ?? '');
        
        if ($virtualAccountNo === '') {
            throw new RuntimeException('Nomor Virtual Account wajib diisi.');
        }
        

        $body = [
            'partnerServiceId' => $partnerServiceId,
            'customerNo' => $customerNo,
            'virtualAccountNo' => $virtualAccountNo,
            'trxId' => (string) ($data['trxId'] ?? ''),
        ];

        \Log::info('DOKU BEFORE JSON BODY', [
            'virtualAccountNo_raw' => $virtualAccountNo,
            'virtualAccountNo_length' => strlen($virtualAccountNo),
            'json' => json_encode($body),
        ]);

        if (! empty($data['paymentRequestId'])) {
            $body['paymentRequestId'] = (string) $data['paymentRequestId'];
        }
        \Log::info('DOKU CHECK STATUS REQUEST', [
            'partnerServiceId' => $partnerServiceId,
            'partnerServiceId_length' => strlen($partnerServiceId),
            'customerNo' => $customerNo,
            'virtualAccountNo' => $virtualAccountNo,
            'virtualAccountNo_length' => strlen($virtualAccountNo),
            'trxId' => $data['trxId'] ?? null,
            'paymentRequestId' => $data['paymentRequestId'] ?? null,
        ]);

        $requestBody = json_encode($body, JSON_UNESCAPED_SLASHES);

        if ($requestBody === false) {
            throw new RuntimeException('Gagal membuat JSON request Check Status DOKU.');
        }

        $signature = $this->generateSymmetricSignature('POST', $endpoint, $accessToken, $requestBody, $timestamp);
        \Log::info('DOKU CHECK STATUS REQUEST', [
            'endpoint' => $endpoint,
            'external_id' => $externalId,
            'body' => $body,
        ]);
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

        $responseData = $response->json();

        \Log::info('DOKU CHECK STATUS RESPONSE', [
            'external_id' => $externalId,
            'response' => $responseData,
        ]);

        if (! is_array($responseData)) {
            throw new RuntimeException('Respons Check Status DOKU tidak valid.');
        }

        return [
            'http_status' => $response->status(),
            'response' => $responseData,
            '_external_id' => $externalId,
        ];
    }
}
