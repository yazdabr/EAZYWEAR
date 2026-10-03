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

    public function test_biteship_webhook_accepts_valid_signature(): void
    {
        config([
            'biteship.webhook.signature_key' => 'X-Eazywear-Biteship-Signature',
            'biteship.webhook.signature_secret' => 'test-secret-value',
        ]);

        $response = $this->postJson(
            '/webhooks/biteship',
            [
                'event' => 'order.status',
                'order_id' => 'TEST-001',
                'status' => 'allocated',
            ],
            [
                'X-Eazywear-Biteship-Signature' => 'test-secret-value',
            ]
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Webhook diterima.',
            ]);
    }

    public function test_biteship_webhook_rejects_invalid_signature(): void
    {
        config([
            'biteship.webhook.signature_key' => 'X-Eazywear-Biteship-Signature',
            'biteship.webhook.signature_secret' => 'test-secret-value',
        ]);

        $response = $this->postJson(
            '/webhooks/biteship',
            [
                'event' => 'order.status',
                'order_id' => 'TEST-001',
                'status' => 'allocated',
            ],
            [
                'X-Eazywear-Biteship-Signature' => 'wrong-signature',
            ]
        );

        $response
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Webhook signature tidak valid.',
            ]);
    }

    public function test_biteship_webhook_rejects_missing_signature(): void
    {
        config([
            'biteship.webhook.signature_key' => 'X-Eazywear-Biteship-Signature',
            'biteship.webhook.signature_secret' => 'test-secret-value',
        ]);

        $response = $this->postJson(
            '/webhooks/biteship',
            [
                'event' => 'order.status',
                'order_id' => 'TEST-001',
                'status' => 'allocated',
            ]
        );

        $response
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Webhook signature tidak valid.',
            ]);
    }

    public function test_biteship_webhook_signature_check_does_not_log_secret_or_signature_value(): void
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
                'X-Eazywear-Biteship-Signature' => 'test-secret-value',
            ]
        );

        $response->assertOk();

        \Log::shouldHaveReceived('info')
            ->withArgs(function ($message, $context) {
                return $message === 'BITESHIP WEBHOOK SIGNATURE CHECK'
                    && $context['matches_secret'] === true
                    && $context['signature_length'] === 17
                    && ! isset($context['signature'])
                    && ! isset($context['secret']);
            });
    }
}
