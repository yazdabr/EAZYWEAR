<?php

namespace App\Support;

final class CourierCode
{
    public const GOJEK = 'gojek';

    /**
     * Normalize a courier code or name for comparison.
     */
    public static function normalize(?string $courier): string
    {
        return strtolower(trim((string) $courier));
    }

    /**
     * Resolve the first available courier identity from the given candidates.
     *
     * Stored courier codes should be passed before any API fallback so the
     * actual courier saved on the transaction/fulfillment/shipment wins.
     */
    public static function resolve(?string ...$candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $normalized = self::normalize($candidate);

            if ($normalized !== '') {
                return $normalized;
            }
        }

        return null;
    }

    /**
     * Live tracking is only supported for Gojek shipments.
     */
    public static function allowsLiveTracking(?string ...$candidates): bool
    {
        return self::resolve(...$candidates) === self::GOJEK;
    }
}
