<?php

namespace Tests\Unit;

use App\Models\FulfillmentSlot;
use App\Services\FulfillmentSlotService;
use App\Models\FulfillmentHold;
use App\Models\Transaction;
use Tests\TestCase;

class FulfillmentSlotServiceTest extends TestCase
{
    private FulfillmentSlotService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(FulfillmentSlotService::class);
    }

    public function test_date_inside_special_batch_is_recognized(): void
    {
        $this->assertTrue(
            $this->service->isSpecialBatchDate('2026-10-30')
        );

        $this->assertTrue(
            $this->service->isSpecialBatchDate('2026-11-03')
        );
    }

    public function test_date_outside_special_batch_is_not_recognized(): void
    {
        $this->assertFalse(
            $this->service->isSpecialBatchDate('2026-10-29')
        );

        $this->assertFalse(
            $this->service->isSpecialBatchDate('2026-11-04')
        );
    }

    public function test_all_special_batch_dates_are_available_when_empty(): void
    {
        FulfillmentSlot::query()->update([
            'used_count' => 0,
        ]);

        $this->assertSame(
            [
                '2026-10-30',
                '2026-10-31',
                '2026-11-01',
                '2026-11-02',
                '2026-11-03',
            ],
            $this->service->getAvailableDates()
        );
    }

    public function test_full_date_is_not_available(): void
    {
        FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->update([
                'used_count' => 100,
            ]);

        $availableDates = $this->service->getAvailableDates();

        $this->assertNotContains('2026-10-30', $availableDates);
        $this->assertContains('2026-10-31', $availableDates);
    }

    public function test_date_with_99_paid_and_1_active_hold_is_not_available(): void
    {
        $slot = FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->firstOrFail();

        $slot->update([
            'used_count' => 99,
            'capacity' => 100,
        ]);

        $transaction = Transaction::factory()->create([
            'status' => 'PENDING',
            'fulfillment_date' => '2026-10-30',
        ]);

        FulfillmentHold::query()->create([
            'transaction_id' => $transaction->id,
            'fulfillment_slot_id' => $slot->id,
            'status' => FulfillmentHold::HELD,
        ]);

        $availableDates = $this->service->getAvailableDates();

        $this->assertNotContains(
            '2026-10-30',
            $availableDates
        );
    }

    public function test_reserve_increases_used_count(): void
    {
        FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->update([
                'used_count' => 0,
            ]);

        $this->service->reserve('2026-10-30');

        $this->assertDatabaseHas('fulfillment_slots', [
            'date' => '2026-10-30',
            'capacity' => 100,
            'used_count' => 1,
        ]);
    }

    public function test_release_decreases_used_count(): void
    {
        FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->update([
                'used_count' => 1,
            ]);

        $this->service->release('2026-10-30');

        $this->assertDatabaseHas('fulfillment_slots', [
            'date' => '2026-10-30',
            'capacity' => 100,
            'used_count' => 0,
        ]);
    }

    public function test_reserve_stops_at_capacity(): void
    {
        FulfillmentSlot::query()
            ->whereDate('date', '2026-10-30')
            ->update([
                'used_count' => 99,
                'capacity' => 100,
            ]);

        // 99 -> 100 harus berhasil.
        $this->service->reserve('2026-10-30');

        $this->assertDatabaseHas('fulfillment_slots', [
            'date' => '2026-10-30',
            'capacity' => 100,
            'used_count' => 100,
        ]);

        // 100 -> 101 harus ditolak.
        try {
            $this->service->reserve('2026-10-30');

            $this->fail('Reserve seharusnya ditolak ketika slot sudah penuh.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertSame(
                'Slot fulfillment untuk tanggal tersebut sudah penuh.',
                $e->validator->errors()->first('fulfillment_date')
            );
        }

        // Pastikan tidak pernah menjadi 101.
        $this->assertDatabaseHas('fulfillment_slots', [
            'date' => '2026-10-30',
            'capacity' => 100,
            'used_count' => 100,
        ]);
    }
}