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

        $providedSignature = $request->header($signatureKey);

        if (! is_string($providedSignature) || $providedSignature === '') {
            return false;
        }

        return hash_equals(
            $signatureSecret,
            $providedSignature
        );
    }
}
