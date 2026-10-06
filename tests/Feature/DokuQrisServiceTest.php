<?php

namespace Tests\Feature;

use App\Services\DokuQrisService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DokuQrisServiceTest extends TestCase
{
    public function test_qris_get_access_token_uses_b2b_token_contract(): void
    {
        config()->set('doku-qris.client_id', 'TEST-QRIS-CLIENT-ID');

        $dummyKeyPath = storage_path(
            'framework/testing/doku-qris-dummy-private.pem'
        );

        if (! is_dir(dirname($dummyKeyPath))) {
            mkdir(dirname($dummyKeyPath), 0755, true);
        }

        file_put_contents($dummyKeyPath, 'TEST-PRIVATE-KEY');

        config()->set(
            'doku-qris.merchant_private_key_path',
            'storage/framework/testing/doku-qris-dummy-private.pem'
        );

        Http::fake([
            'https://api-sandbox.doku.com/authorization/v1/access-token/b2b' =>
                Http::response([
                    'accessToken' => 'fake-qris-access-token',
                    'tokenType' => 'Bearer',
                    'expiresIn' => 900,
                ], 200),
        ]);

        $service = new class extends DokuQrisService {
            protected function createB2bTokenSignature(
                string $stringToSign,
                string $privateKey
            ): string {
                return 'fake-rsa-signature';
            }
        };

        try {
            $response = $service->getAccessToken();

            $this->assertSame(
                'fake-qris-access-token',
                $response['accessToken']
            );

            Http::assertSent(function ($request) {
                $this->assertSame('POST', $request->method());

                $this->assertSame(
                    'https://api-sandbox.doku.com/authorization/v1/access-token/b2b',
                    $request->url()
                );

                $this->assertSame(
                    'client_credentials',
                    $request['grantType']
                );

                $this->assertSame(
                    'fake-rsa-signature',
                    $request->header('X-SIGNATURE')[0] ?? null
                );

                $this->assertNotEmpty(
                    $request->header('X-TIMESTAMP')[0] ?? null
                );

                $this->assertSame(
                    'TEST-QRIS-CLIENT-ID',
                    $request->header('X-CLIENT-KEY')[0] ?? null
                );

                $this->assertSame(
                    'application/json',
                    $request->header('Content-Type')[0] ?? null
                );

                return true;
            });
        } finally {
            if (file_exists($dummyKeyPath)) {
                unlink($dummyKeyPath);
            }
        }
    }

    public function test_qris_generate_qr_uses_generate_contract(): void
    {
        config()->set('doku-qris.base_url', 'https://api-sandbox.doku.com');
        config()->set('doku-qris.client_id', 'TEST-QRIS-CLIENT-ID');
        config()->set('doku-qris.client_secret', 'TEST-QRIS-CLIENT-SECRET');
        config()->set('doku-qris.channel_id', 'H2H');
        config()->set(
            'doku-qris.merchant_id',
            'TEST-MERCHANT-ID'
        );
        config()->set(
            'doku-qris.terminal_id',
            'TEST-TERMINAL-ID'
        );
        config()->set(
            'doku-qris.postal_code',
            '70124'
        );
        config()->set(
            'doku-qris.fee_type',
            '1'
        );

        Http::fake([
            'https://api-sandbox.doku.com/authorization/v1/access-token/b2b' =>
                Http::response([
                    'accessToken' => 'fake-qris-access-token',
                    'tokenType' => 'Bearer',
                    'expiresIn' => 900,
                ], 200),

            'https://api-sandbox.doku.com/snap-adapter/b2b/v1.0/qr/qr-mpm-generate' =>
                Http::response([
                    'responseCode' => '2004700',
                    'responseMessage' => 'Successful',
                    'referenceNo' => 'DOKU-QRIS-REF-001',
                    'partnerReferenceNo' => 'INV-TEST-QRIS-001',
                    'qrContent' => '000201010212...',
                    'terminalId' => 'TEST-TERMINAL-ID',
                ], 200),
        ]);

        $dummyKeyPath = storage_path(
            'framework/testing/doku-qris-dummy-private.pem'
        );

        if (! is_dir(dirname($dummyKeyPath))) {
            mkdir(dirname($dummyKeyPath), 0755, true);
        }

        file_put_contents($dummyKeyPath, 'TEST-PRIVATE-KEY');

        config()->set(
            'doku-qris.merchant_private_key_path',
            'storage/framework/testing/doku-qris-dummy-private.pem'
        );

        $service = new class extends DokuQrisService {
            protected function createB2bTokenSignature(
                string $stringToSign,
                string $privateKey
            ): string {
                return 'fake-rsa-signature';
            }

            public function generateExternalId(): string
            {
                return '2026100512345678';
            }
        };

        try {
            $response = $service->generateQr(
                'INV-TEST-QRIS-001',
                150000
            );

            $this->assertSame(
                '2004700',
                $response['responseCode']
            );

            $this->assertSame(
                'DOKU-QRIS-REF-001',
                $response['referenceNo']
            );

            $this->assertSame(
                '000201010212...',
                $response['qrContent']
            );

            $this->assertSame(
                '2026100512345678',
                $response['_external_id']
            );

            Http::assertSent(function ($request) {
                if (
                    $request->url()
                    !== 'https://api-sandbox.doku.com/snap-adapter/b2b/v1.0/qr/qr-mpm-generate'
                ) {
                    return false;
                }

                $this->assertSame(
                    'POST',
                    $request->method()
                );

                $this->assertSame(
                    'TEST-QRIS-CLIENT-ID',
                    $request->header('X-PARTNER-ID')[0] ?? null
                );

                $this->assertSame(
                    '2026100512345678',
                    $request->header('X-EXTERNAL-ID')[0] ?? null
                );

                $this->assertSame(
                    'H2H',
                    $request->header('CHANNEL-ID')[0] ?? null
                );

                $this->assertSame(
                    'Bearer fake-qris-access-token',
                    $request->header('Authorization')[0] ?? null
                );

                $this->assertNotEmpty(
                    $request->header('X-TIMESTAMP')[0] ?? null
                );

                $this->assertNotEmpty(
                    $request->header('X-SIGNATURE')[0] ?? null
                );

                $payload = $request->data();

                $this->assertSame(
                    'INV-TEST-QRIS-001',
                    $payload['partnerReferenceNo']
                );

                $this->assertSame(
                    '150000.00',
                    $payload['amount']['value']
                );

                $this->assertSame(
                    'IDR',
                    $payload['amount']['currency']
                );

                $this->assertSame(
                    'TEST-MERCHANT-ID',
                    $payload['merchantId']
                );

                $this->assertSame(
                    'TEST-TERMINAL-ID',
                    $payload['terminalId']
                );

                $this->assertSame(
                    '70124',
                    $payload['additionalInfo']['postalCode']
                );

                $this->assertSame(
                    '1',
                    $payload['additionalInfo']['feeType']
                );

                return true;
            });
        } finally {
            if (file_exists($dummyKeyPath)) {
                unlink($dummyKeyPath);
            }
        }
    }

    public function test_qris_query_uses_query_contract(): void
    {
        config()->set(
            'doku-qris.base_url',
            'https://api-sandbox.doku.com'
        );

        config()->set(
            'doku-qris.client_id',
            'TEST-QRIS-CLIENT-ID'
        );

        config()->set(
            'doku-qris.client_secret',
            'TEST-QRIS-CLIENT-SECRET'
        );

        config()->set(
            'doku-qris.channel_id',
            'H2H'
        );

        config()->set(
            'doku-qris.merchant_id',
            'TEST-MERCHANT-ID'
        );

        Http::fake([
            'https://api-sandbox.doku.com/authorization/v1/access-token/b2b' =>
                Http::response([
                    'accessToken' => 'fake-qris-access-token',
                    'tokenType' => 'Bearer',
                    'expiresIn' => 900,
                ], 200),

            'https://api-sandbox.doku.com/snap-adapter/b2b/v1.0/qr/qr-mpm-query' =>
                Http::response([
                    'responseCode' => '2005100',
                    'responseMessage' => 'Request has been processed successfully',
                    'originalReferenceNo' => 'DOKU-QRIS-REF-001',
                    'originalPartnerReferenceNo' => 'INV-TEST-QRIS-001',
                    'serviceCode' => '47',
                    'latestTransactionStatus' => '00',
                    'transactionStatusDesc' => 'Success',
                    'paidTime' => '2026-10-05T17:44:19+07:00',
                    'amount' => [
                        'value' => 150000,
                        'currency' => 'IDR',
                    ],
                ], 200),
        ]);

        $dummyKeyPath = storage_path(
            'framework/testing/doku-qris-dummy-private.pem'
        );

        if (! is_dir(dirname($dummyKeyPath))) {
            mkdir(dirname($dummyKeyPath), 0755, true);
        }

        file_put_contents(
            $dummyKeyPath,
            'TEST-PRIVATE-KEY'
        );

        config()->set(
            'doku-qris.merchant_private_key_path',
            'storage/framework/testing/doku-qris-dummy-private.pem'
        );

        $service = new class extends DokuQrisService {
            protected function createB2bTokenSignature(
                string $stringToSign,
                string $privateKey
            ): string {
                return 'fake-rsa-signature';
            }

            public function generateExternalId(): string
            {
                return '2026100512345678';
            }
        };

        try {
            $response = $service->queryQr(
                'DOKU-QRIS-REF-001',
                'INV-TEST-QRIS-001'
            );

            $this->assertSame(
                '2005100',
                $response['responseCode']
            );

            $this->assertSame(
                'DOKU-QRIS-REF-001',
                $response['originalReferenceNo']
            );

            $this->assertSame(
                'INV-TEST-QRIS-001',
                $response['originalPartnerReferenceNo']
            );

            $this->assertSame(
                '47',
                $response['serviceCode']
            );

            $this->assertSame(
                '00',
                $response['latestTransactionStatus']
            );

            $this->assertSame(
                'Success',
                $response['transactionStatusDesc']
            );

            $this->assertSame(
                '2026-10-05T17:44:19+07:00',
                $response['paidTime']
            );

            $this->assertSame(
                150000,
                $response['amount']['value']
            );

            $this->assertSame(
                'IDR',
                $response['amount']['currency']
            );

            Http::assertSent(function ($request) {
                if (
                    $request->url()
                    !== 'https://api-sandbox.doku.com/snap-adapter/b2b/v1.0/qr/qr-mpm-query'
                ) {
                    return false;
                }

                $this->assertSame(
                    'POST',
                    $request->method()
                );

                $this->assertSame(
                    'TEST-QRIS-CLIENT-ID',
                    $request->header('X-PARTNER-ID')[0] ?? null
                );

                $this->assertSame(
                    '2026100512345678',
                    $request->header('X-EXTERNAL-ID')[0] ?? null
                );

                $this->assertSame(
                    'H2H',
                    $request->header('CHANNEL-ID')[0] ?? null
                );

                $this->assertSame(
                    'Bearer fake-qris-access-token',
                    $request->header('Authorization')[0] ?? null
                );

                $this->assertNotEmpty(
                    $request->header('X-TIMESTAMP')[0] ?? null
                );

                $this->assertNotEmpty(
                    $request->header('X-SIGNATURE')[0] ?? null
                );

                $payload = $request->data();

                $this->assertSame(
                    'DOKU-QRIS-REF-001',
                    $payload['originalReferenceNo']
                );

                $this->assertSame(
                    'INV-TEST-QRIS-001',
                    $payload['originalPartnerReferenceNo']
                );

                $this->assertSame(
                    'TEST-MERCHANT-ID',
                    $payload['merchantId']
                );

                $this->assertSame(
                    '47',
                    $payload['serviceCode']
                );

                return true;
            });
        } finally {
            if (file_exists($dummyKeyPath)) {
                unlink($dummyKeyPath);
            }
        }
    }
}