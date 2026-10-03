<?php

namespace Tests\Feature;

use Tests\TestCase;

class BiteshipWebhookTest extends TestCase
{
    public function test_biteship_webhook_fails_closed_when_signature_protocol_is_not_configured(): void
    {
        config([
            'biteship.webhook.signature_key' => null,
            'biteship.webhook.signature_secret' => null,
        ]);

        $response = $this->postJson('/webhooks/biteship', [
            'event' => 'order.status',
            'status' => 'delivered',
            'order_id' => 'TEST-001',
        ]);

        $response
            ->assertStatus(503)
            ->assertJson([
                'success' => false,
                'message' => 'Webhook belum dapat diproses.',
            ]);
    }

    public function test_biteship_webhook_returns_ok_for_empty_json_installation_request(): void
    {
        config([
            'biteship.webhook.signature_key' => null,
            'biteship.webhook.signature_secret' => null,
        ]);

        $response = $this->call(
            'POST',
            '/webhooks/biteship',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            ''
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Webhook endpoint ready.',
            ]);
    }

    public function test_biteship_webhook_logs_header_metadata_without_logging_secret(): void
    {
        config([
            'biteship.webhook.signature_key' => 'X-Eazywear-Biteship-Signature',
            'biteship.webhook.signature_secret' => 'test-secret-value',
        ]);

        \Log::spy();

        $response = $this->postJson(
            '/webhooks/biteship',
            [
                'event' => 'order.status',
                'order_id' => 'TEST-001',
                'status' => 'allocated',
            ],
            [
                'X-Eazywear-Biteship-Signature' => 'abcdef1234567890',
            ]
        );

        $response->assertStatus(503);

        \Log::shouldHaveReceived('info')
            ->withArgs(function ($message, $context) {
                $signatureHeader = 'x-eazywear-biteship-signature';

                return $message === 'BITESHIP WEBHOOK DIAGNOSTIC'
                    && in_array(
                        $signatureHeader,
                        $context['header_names'],
                        true
                    )
                    && isset($context['headers_metadata'][$signatureHeader])
                    && $context['headers_metadata'][$signatureHeader]['length'] === 16
                    && ! isset(
                        $context['headers_metadata'][$signatureHeader]['value']
                    );
            });
    }
}
