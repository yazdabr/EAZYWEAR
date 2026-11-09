<?php

namespace Tests\Unit;

use App\Services\FulfillmentDateService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PickupBreakTimeTest extends TestCase
{
    private FulfillmentDateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(FulfillmentDateService::class);
    }

    public function test_pickup_at_1200_is_allowed(): void
    {
        $this->service->validatePickupTime(
            '12:00',
            '2026-11-04',
            Carbon::parse('2026-11-04 08:00')
        );

        $this->assertTrue(true);
    }

    public function test_pickup_at_1259_is_allowed(): void
    {
        $this->service->validatePickupTime(
            '12:59',
            '2026-11-04',
            Carbon::parse('2026-11-04 08:00')
        );

        $this->assertTrue(true);
    }

    public function test_pickup_at_1300_is_rejected(): void
    {
        $this->assertPickupTimeRejected('13:00');
    }

    public function test_pickup_at_1330_is_rejected(): void
    {
        $this->assertPickupTimeRejected('13:30');
    }

    public function test_pickup_at_1359_is_rejected(): void
    {
        $this->assertPickupTimeRejected('13:59');
    }

    public function test_pickup_at_1400_is_allowed(): void
    {
        $this->service->validatePickupTime(
            '14:00',
            '2026-11-04',
            Carbon::parse('2026-11-04 08:00')
        );

        $this->assertTrue(true);
    }

    public function test_pickup_at_1500_is_allowed(): void
    {
        $this->service->validatePickupTime(
            '15:00',
            '2026-11-04',
            Carbon::parse('2026-11-04 08:00')
        );

        $this->assertTrue(true);
    }

    public function test_pickup_break_time_rejects_the_correct_field(): void
    {
        try {
            $this->service->validatePickupTime(
                '13:30',
                '2026-11-04',
                Carbon::parse('2026-11-04 08:00'),
                'ready_pickup_time_end'
            );

            $this->fail('Jadwal pickup pada jam istirahat seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'ready_pickup_time_end',
                $exception->errors()
            );
        }
    }

    private function assertPickupTimeRejected(string $pickupTime): void
    {
        try {
            $this->service->validatePickupTime(
                $pickupTime,
                '2026-11-04',
                Carbon::parse('2026-11-04 08:00')
            );

            $this->fail("Jadwal pickup pukul {$pickupTime} seharusnya ditolak.");
        } catch (ValidationException $exception) {
            $this->assertStringContainsString(
                'jam istirahat',
                $exception->errors()['pickup_time_start'][0] ?? ''
            );
        }
    }
}
