<?php

namespace App\Services;

use App\Models\FulfillmentSlot;
use Carbon\Carbon;
use App\Models\FulfillmentHold;
use Illuminate\Validation\ValidationException;

class FulfillmentSlotService
{
    /**
     * Determine whether a date belongs to the special fulfillment batch.
     */
    public function isSpecialBatchDate(Carbon|string $date): bool
    {
        $date = $date instanceof Carbon
            ? $date->copy()->startOfDay()
            : Carbon::parse($date)->startOfDay();

        $startDate = Carbon::parse(
            config('fulfillment.special_batch.start_date')
        )->startOfDay();

        $endDate = Carbon::parse(
            config('fulfillment.special_batch.end_date')
        )->startOfDay();

        return $date->betweenIncluded($startDate, $endDate);
    }

    /**
     * Get the fulfillment slot for a specific special-batch date.
     */
    public function getSlot(Carbon|string $date): ?FulfillmentSlot
    {
        $date = $date instanceof Carbon
            ? $date->toDateString()
            : Carbon::parse($date)->toDateString();

        return FulfillmentSlot::query()
            ->whereDate('date', $date)
            ->first();
    }

    /**
     * Get all available special-batch fulfillment dates.
     */
    public function getAvailableDates(): array
    {
        $startDate = Carbon::parse(
            config('fulfillment.special_batch.start_date')
        );

        $endDate = Carbon::parse(
            config('fulfillment.special_batch.end_date')
        );

        return FulfillmentSlot::query()
            ->whereBetween('date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->whereRaw(
                '(used_count + (
                    SELECT COUNT(*)
                    FROM fulfillment_holds
                    WHERE fulfillment_holds.fulfillment_slot_id = fulfillment_slots.id
                    AND fulfillment_holds.status = ?
                )) < capacity',
                [FulfillmentHold::HELD]
            )
            ->orderBy('date')
            ->pluck('date')
            ->map(fn ($date) => $date->toDateString())
            ->values()
            ->all();
    }

    /**
     * Validate that a special-batch date still has capacity.
     */
    public function ensureDateAvailable(Carbon|string $date): FulfillmentSlot
    {
        $date = $date instanceof Carbon
            ? $date->copy()->startOfDay()
            : Carbon::parse($date)->startOfDay();

        if (! $this->isSpecialBatchDate($date)) {
            throw ValidationException::withMessages([
                'pickup_date' => 'Tanggal fulfillment tidak tersedia untuk batch ini.',
            ]);
        }

        $slot = $this->getSlot($date);

        if (! $slot) {
            throw ValidationException::withMessages([
                'pickup_date' => 'Tanggal fulfillment tidak tersedia.',
            ]);
        }

        $activeHoldCount = $slot->fulfillmentHolds()
            ->where('status', FulfillmentHold::HELD)
            ->count();

        if (($slot->used_count + $activeHoldCount) >= $slot->capacity) {
            throw ValidationException::withMessages([
                'pickup_date' => 'Tanggal pickup tersebut sudah penuh. Silakan pilih tanggal lain.',
            ]);
        }

        return $slot;
    }

    /**
     * Reserve one fulfillment slot after payment is confirmed.
     *
     * The caller must execute this inside an active database transaction.
     */
    public function reserve(Carbon|string $date): FulfillmentSlot
    {
        $date = $date instanceof Carbon
            ? $date->copy()->startOfDay()
            : Carbon::parse($date)->startOfDay();

        if (! $this->isSpecialBatchDate($date)) {
            throw ValidationException::withMessages([
                'fulfillment_date' => 'Tanggal fulfillment tidak tersedia untuk batch ini.',
            ]);
        }

        $slot = FulfillmentSlot::query()
            ->whereDate('date', $date->toDateString())
            ->lockForUpdate()
            ->first();

        if (! $slot) {
            throw ValidationException::withMessages([
                'fulfillment_date' => 'Slot fulfillment tidak tersedia.',
            ]);
        }

        if ($slot->used_count >= $slot->capacity) {
            throw ValidationException::withMessages([
                'fulfillment_date' => 'Slot fulfillment untuk tanggal tersebut sudah penuh.',
            ]);
        }

        $slot->increment('used_count');

        $slot->refresh();

        return $slot;
    }

    /**
     * Release one previously reserved fulfillment slot.
     *
     * The caller must execute this inside an active database transaction.
     */
    public function release(Carbon|string $date): void
    {
        $date = $date instanceof Carbon
            ? $date->copy()->startOfDay()
            : Carbon::parse($date)->startOfDay();

        if (! $this->isSpecialBatchDate($date)) {
            return;
        }

        $slot = FulfillmentSlot::query()
            ->whereDate('date', $date->toDateString())
            ->lockForUpdate()
            ->first();

        if (! $slot || $slot->used_count <= 0) {
            return;
        }

        $slot->decrement('used_count');
    }
}