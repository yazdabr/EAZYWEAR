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

        $headers = collect($request->headers->all())
            ->mapWithKeys(function (array $values, string $name): array {
                $value = (string) ($values[0] ?? '');

                return [
                    strtolower($name) => [
                        'length' => strlen($value),
                        'has_value' => $value !== '',
                        'looks_hex' => $value !== '' && ctype_xdigit($value),
                        'looks_base64' => $value !== ''
                            && base64_encode(base64_decode($value, true)) === $value,
                    ],
                ];
            })
            ->all();

        \Log::info('BITESHIP WEBHOOK DIAGNOSTIC', [
            'content_type' => $request->header('Content-Type'),
            'method' => $request->method(),
            'header_names' => array_keys($headers),
            'headers_metadata' => $headers,

            'configured_signature_key' => is_string($signatureKey)
                ? $signatureKey
                : null,

            'signature_key_present' => is_string($signatureKey)
                && $signatureKey !== ''
                && $request->hasHeader($signatureKey),

            'signature_secret_configured' => is_string($signatureSecret)
                && trim($signatureSecret) !== '',

            'payload_length' => strlen($request->getContent()),
            'payload_sha256' => hash('sha256', $request->getContent()),
        ]);

        throw new RuntimeException(
            'Protocol signature webhook Biteship belum terkonfirmasi.'
        );
    }
}
