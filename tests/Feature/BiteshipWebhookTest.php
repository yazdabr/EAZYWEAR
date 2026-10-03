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
}
