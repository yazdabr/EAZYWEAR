<?php

namespace Tests\Unit;

use App\Support\CourierCode;
use Tests\TestCase;

class CourierCodeTest extends TestCase
{
    public function test_normalize_lowercases_and_trims_courier_code(): void
    {
        $this->assertSame('gojek', CourierCode::normalize('  GoJeK  '));
        $this->assertSame('jnt', CourierCode::normalize(' JNT '));
        $this->assertSame('', CourierCode::normalize(null));
    }

    public function test_live_tracking_is_only_allowed_for_gojek(): void
    {
        $this->assertTrue(CourierCode::allowsLiveTracking('gojek'));
        $this->assertTrue(CourierCode::allowsLiveTracking(' GOJEK '));

        $this->assertFalse(CourierCode::allowsLiveTracking('jnt'));
        $this->assertFalse(CourierCode::allowsLiveTracking('lion'));
        $this->assertFalse(CourierCode::allowsLiveTracking('jne'));
        $this->assertFalse(CourierCode::allowsLiveTracking(''));
        $this->assertFalse(CourierCode::allowsLiveTracking(null));
    }

    public function test_resolve_prefers_the_first_known_courier_identity(): void
    {
        $this->assertSame(
            'jnt',
            CourierCode::resolve('jnt', 'gojek')
        );

        $this->assertSame(
            'gojek',
            CourierCode::resolve(null, ' Gojek ')
        );

        $this->assertNull(CourierCode::resolve(null, '   '));
    }
}
