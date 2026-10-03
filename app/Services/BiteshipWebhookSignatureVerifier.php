<?php

namespace App\Services;

use Illuminate\Http\Request;
use RuntimeException;

class BiteshipWebhookSignatureVerifier
{
    public function verify(Request $request): bool
    {
        $signatureKey = config('biteship.webhook.signature_key');
        $signatureSecret = config('biteship.webhook.signature_secret');

        if (
            ! is_string($signatureKey)
            || trim($signatureKey) === ''
            || ! is_string($signatureSecret)
            || trim($signatureSecret) === ''
        ) {
            throw new RuntimeException(
                'Konfigurasi signature webhook Biteship belum lengkap.'
            );
        }

        /*
         * IMPORTANT:
         *
         * Biteship dashboard confirms that a Signature Key and
         * Signature Secret are configured and sent with webhook
         * requests.
         *
         * The exact outbound header names and signature algorithm
         * have not yet been confirmed.
         *
         * Do NOT implement guessed HMAC/header logic here.
         */
        throw new RuntimeException(
            'Protocol signature webhook Biteship belum terkonfirmasi.'
        );
    }
}
