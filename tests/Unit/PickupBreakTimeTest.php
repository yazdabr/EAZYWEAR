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

    public function test_pickup_range_ending_at_break_end_is_adjusted_to_break_start(): void
    {
        $range = $this->service->normalizePickupTimeRange('12:00', '14:00');

        $this->assertSame(['12:00', '13:00'], $range);
    }

    public function test_pickup_range_crossing_break_is_kept(): void
    {
        $range = $this->service->normalizePickupTimeRange('12:00', '15:00');

        $this->assertSame(['12:00', '15:00'], $range);
    }

    public function test_pickup_range_starting_at_break_start_is_kept(): void
    {
        $range = $this->service->normalizePickupTimeRange('13:00', '15:00');

        $this->assertSame(['13:00', '15:00'], $range);
    }

    public function test_pickup_range_ending_at_break_start_is_kept(): void
    {
        $range = $this->service->normalizePickupTimeRange('12:00', '13:00');

        $this->assertSame(['12:00', '13:00'], $range);
    }

    public function test_pickup_range_ending_inside_break_is_kept_for_advisory_only(): void
    {
        $range = $this->service->normalizePickupTimeRange('12:00', '13:30');

        $this->assertSame(['12:00', '13:30'], $range);
    }

    public function test_pickup_range_starting_inside_break_is_rejected(): void
    {
        try {
            $this->service->normalizePickupTimeRange(
                '13:30',
                '14:30',
                'ready_pickup_time_start',
                'ready_pickup_time_end'
            );

            $this->fail('Jadwal mulai di dalam jam istirahat seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'ready_pickup_time_start',
                $exception->errors()
            );
            $this->assertStringContainsString(
                'jam istirahat',
                $exception->errors()['ready_pickup_time_start'][0] ?? ''
            );
        }
    }

    public function test_pickup_range_ending_at_break_end_when_adjustment_is_invalid_is_rejected(): void
    {
        try {
            $this->service->normalizePickupTimeRange(
                '13:00',
                '14:00',
                'ready_pickup_time_start',
                'ready_pickup_time_end'
            );

            $this->fail('Jadwal 13:00-14:00 seharusnya ditolak karena tidak dapat disesuaikan.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'ready_pickup_time_end',
                $exception->errors()
            );
            $this->assertStringContainsString(
                'jam istirahat',
                $exception->errors()['ready_pickup_time_end'][0] ?? ''
            );
        }
    }

    public function test_adjusted_end_time_is_always_after_start_time(): void
    {
        [$start, $end] = $this->service->normalizePickupTimeRange('12:00', '14:00');

        $this->assertGreaterThan($start, $end);
    }

    public function test_pickup_time_at_break_start_is_allowed_as_a_range_boundary(): void
    {
        $this->service->validatePickupTime(
            '13:00',
            '2026-11-04',
            Carbon::parse('2026-11-04 08:00')
        );

        $this->assertTrue(true);
    }

    public function test_pickup_time_at_break_end_is_allowed_as_a_range_boundary(): void
    {
        $this->service->validatePickupTime(
            '14:00',
            '2026-11-04',
            Carbon::parse('2026-11-04 08:00')
        );

        $this->assertTrue(true);
    }
}
