<?php

namespace Tests\Unit;

use App\Models\FulfillmentSlot;
use App\Services\FulfillmentDateService;
use Illuminate\Validation\ValidationException;
use App\Models\FulfillmentHold;
use App\Models\Transaction;
use Tests\TestCase;
use Carbon\Carbon;

class FulfillmentDateServiceTest extends TestCase
{
    private FulfillmentDateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(FulfillmentDateService::class);

        FulfillmentSlot::query()->update([
            'used_count' => 0,
            'capacity' => 100,
        ]);
    }

    public function test_pickup_uses_selected_available_special_batch_date(): void
    {
        $date = $this->service->determineForPickup('2026-10-31');

        $this->assertSame(
            '2026-10-31',
            $date->toDateString()
        );
    }

    public function test_pickup_rejects_full_special_batch_date(): void
    {
        FulfillmentSlot::query()
            ->whereDate('date', '2026-10-31')
            ->update([
                'used_count' => 100,
            ]);

        $this->expectException(ValidationException::class);

        $this->service->determineForPickup('2026-10-31');
    }

    public function test_courier_uses_earliest_available_special_batch_date(): void
    {
        FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->update([
                'used_count' => 100,
            ]);

        $date = $this->service->determineForCourier();

        $this->assertSame(
            '2026-10-31',
            $date->toDateString()
        );
    }

    public function test_courier_skips_multiple_full_dates(): void
    {
        FulfillmentSlot::query()
            ->whereIn('date', [
                '2026-10-30',
                '2026-10-31',
                '2026-11-01',
            ])
            ->update([
                'used_count' => 100,
            ]);

        $date = $this->service->determineForCourier();

        $this->assertSame(
            '2026-11-02',
            $date->toDateString()
        );
    }

    public function test_courier_rejects_when_all_special_batch_dates_are_full(): void
    {
        FulfillmentSlot::query()->update([
            'used_count' => 100,
        ]);

        $this->expectException(ValidationException::class);

        $this->service->determineForCourier();
    }
    public function test_normal_pickup_allows_same_day_before_cutoff(): void
    {
        $date = $this->service->determineForNormalPickup(
            \Carbon\Carbon::parse('2026-11-04 10:00')
        );

        $this->assertSame(
            '2026-11-04',
            $date->toDateString()
        );
    }

    public function test_normal_pickup_allows_same_day_at_cutoff(): void
    {
        $date = $this->service->determineForNormalPickup(
            \Carbon\Carbon::parse('2026-11-04 14:00')
        );

        $this->assertSame(
            '2026-11-04',
            $date->toDateString()
        );
    }

    public function test_normal_pickup_moves_to_next_day_after_cutoff(): void
    {
        $date = $this->service->determineForNormalPickup(
            \Carbon\Carbon::parse('2026-11-04 14:01')
        );

        $this->assertSame(
            '2026-11-05',
            $date->toDateString()
        );
    }

    public function test_normal_pickup_does_not_use_special_batch_quota(): void
    {
        FulfillmentSlot::query()->update([
            'used_count' => 100,
        ]);

        $date = $this->service->determineForNormalPickup(
            \Carbon\Carbon::parse('2026-11-04 10:00')
        );

        $this->assertSame(
            '2026-11-04',
            $date->toDateString()
        );
    }
    public function test_pickup_time_at_09_is_allowed(): void
    {
        $this->service->validatePickupTime(
            '09:00',
            '2026-11-04',
            \Carbon\Carbon::parse('2026-11-04 08:00')
        );

        $this->assertTrue(true);
    }

    public function test_courier_skips_special_batch_date_filled_by_active_hold(): void
    {
        $slot = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->firstOrFail();

        $slot->update([
            'used_count' => 99,
            'capacity' => 100,
        ]);

        $transaction = Transaction::factory()->create();

        FulfillmentHold::query()->create([
            'transaction_id' => $transaction->id,
            'fulfillment_slot_id' => $slot->id,
            'status' => FulfillmentHold::HELD,
        ]);

        $date = $this->service->determineForCourier();

        $this->assertSame(
            '2026-10-31',
            $date->toDateString()
        );
    }

    public function test_pickup_rejects_special_batch_date_when_active_hold_fills_capacity(): void
    {
        $slot = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-31')
            ->firstOrFail();

        $slot->update([
            'used_count' => 99,
            'capacity' => 100,
        ]);

        $transaction = Transaction::factory()->create();

        FulfillmentHold::query()->create([
            'transaction_id' => $transaction->id,
            'fulfillment_slot_id' => $slot->id,
            'status' => FulfillmentHold::HELD,
        ]);

        $this->expectException(ValidationException::class);

        $this->service->determineForPickup('2026-10-31');
    }

    public function test_pickup_time_at_17_is_allowed(): void
    {
        $this->service->validatePickupTime(
            '17:00',
            '2026-11-04',
            \Carbon\Carbon::parse('2026-11-04 10:00')
        );

        $this->assertTrue(true);
    }

    public function test_pickup_time_before_09_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->validatePickupTime(
            '08:59',
            '2026-11-04',
            \Carbon\Carbon::parse('2026-11-04 08:00')
        );
    }

    public function test_pickup_time_after_17_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->validatePickupTime(
            '17:01',
            '2026-11-04',
            \Carbon\Carbon::parse('2026-11-04 10:00')
        );
    }

    public function test_same_day_pickup_before_checkout_time_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->validatePickupTime(
            '09:30',
            '2026-11-04',
            \Carbon\Carbon::parse('2026-11-04 10:00')
        );
    }

    public function test_same_day_pickup_at_checkout_time_is_allowed(): void
    {
        $this->service->validatePickupTime(
            '10:00',
            '2026-11-04',
            \Carbon\Carbon::parse('2026-11-04 10:00')
        );

        $this->assertTrue(true);
    }

    public function test_future_date_pickup_time_does_not_have_to_be_after_checkout_time(): void
    {
        $this->service->validatePickupTime(
            '09:00',
            '2026-11-05',
            \Carbon\Carbon::parse('2026-11-04 16:00')
        );

        $this->assertTrue(true);
    }
    public function test_determine_pickup_special_batch_uses_customer_selected_date(): void
    {
        $date = $this->service->determine(
            'Ambil di Tempat',
            \Carbon\Carbon::parse('2026-10-28 10:00'),
            '2026-10-31'
        );

        $this->assertSame(
            '2026-10-31',
            $date->toDateString()
        );
    }
    public function test_determine_courier_special_batch_uses_earliest_available_date(): void
    {
        FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->update([
                'used_count' => 100,
            ]);

        $date = $this->service->determine(
            'Kurir',
            \Carbon\Carbon::parse('2026-10-30 10:00')
        );

        $this->assertSame(
            '2026-10-31',
            $date->toDateString()
        );
    }
    public function test_determine_pickup_normal_before_cutoff_uses_same_day(): void
    {
        $date = $this->service->determine(
            'Ambil di Tempat',
            \Carbon\Carbon::parse('2026-11-04 10:00'),
            '2026-11-04'
        );

        $this->assertSame(
            '2026-11-04',
            $date->toDateString()
        );
    }
    public function test_determine_courier_normal_uses_checkout_date(): void
    {
        $date = $this->service->determine(
            'Kurir',
            \Carbon\Carbon::parse('2026-11-04 10:00')
        );

        $this->assertSame(
            '2026-11-04',
            $date->toDateString()
        );
    }
    public function test_determine_pickup_requires_pickup_date(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->determine(
            'Ambil di Tempat',
            \Carbon\Carbon::parse('2026-11-04 10:00')
        );
    }
    public function test_determine_rejects_invalid_shipping_method(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->determine(
            'Jemput Tetangga',
            \Carbon\Carbon::parse('2026-11-04 10:00')
        );
    }
    public function test_determine_pickup_normal_rejects_date_before_earliest_allowed_date(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->determine(
            'Ambil di Tempat',
            Carbon::parse('2026-11-04 15:00'),
            '2026-11-04'
        );
    }

    public function test_determine_pickup_normal_allows_tomorrow_after_cutoff(): void
    {
        $date = $this->service->determine(
            'Ambil di Tempat',
            Carbon::parse('2026-11-04 15:00'),
            '2026-11-05'
        );

        $this->assertSame('2026-11-05', $date->toDateString());
    }

    public function test_determine_pickup_normal_allows_far_future_date(): void
    {
        $date = $this->service->determine(
            'Ambil di Tempat',
            Carbon::parse('2026-11-04 15:00'),
            '2026-11-20'
        );

        $this->assertSame('2026-11-20', $date->toDateString());
    }
    public function test_determine_courier_before_special_batch_uses_earliest_available_date(): void
    {
        $date = $this->service->determine(
            'Kurir',
            Carbon::parse('2026-10-20 10:00')
        );

        $this->assertSame('2026-10-30', $date->toDateString());
    }
    public function test_determine_courier_after_special_batch_uses_normal_date(): void
    {
        $date = $this->service->determine(
            'Kurir',
            Carbon::parse('2026-11-04 10:00')
        );

        $this->assertSame('2026-11-04', $date->toDateString());
    }
}