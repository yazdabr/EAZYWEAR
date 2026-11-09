<?php

namespace App\Services;

 
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use App\Services\FulfillmentSlotService;

class FulfillmentDateService
{

    private function isSpecialBatchApplicable(Carbon $checkoutAt): bool
    {
        $endDate = Carbon::parse(
            config('fulfillment.special_batch.end_date')
        )->endOfDay();

        return $checkoutAt->lte($endDate);
    }
    public function __construct(
        private FulfillmentSlotService $slotService
    ) {
    }
    public function determineForPickup(Carbon|string $date): Carbon
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

        if ($this->slotService->isSpecialBatchDate($date)) {
            $this->slotService->ensureDateAvailable($date);

            return $date;
        }

        return $date;
    }

    public function determineForCourier(): Carbon
    {
        $availableDates = $this->slotService->getAvailableDates();

        if ($availableDates === []) {
            throw ValidationException::withMessages([
                'fulfillment_date' => 'Tidak ada tanggal fulfillment yang masih tersedia untuk batch ini.',
            ]);
        }

        return Carbon::parse($availableDates[0])->startOfDay();
    }

    public function determineForNormalPickup(Carbon $checkoutAt): Carbon
    {
        $checkoutAt = $checkoutAt->copy();

        $cutoff = $checkoutAt->copy()
            ->setTimeFromTimeString(
                config('fulfillment.pickup.same_day_cutoff')
            );

        if ($checkoutAt->lt($cutoff)) {
            return $checkoutAt->copy()->startOfDay();
        }

        return $checkoutAt->copy()
            ->addDay()
            ->startOfDay();
    }
    public function validatePickupTime(
        string $pickupTime,
        Carbon|string $pickupDate,
        Carbon $checkoutAt,
        string $field = 'pickup_time_start'
    ): void {
        $pickupDate = $pickupDate instanceof Carbon
            ? $pickupDate->copy()->startOfDay()
            : Carbon::parse($pickupDate)->startOfDay();

        $checkoutAt = $checkoutAt->copy();

        $startTime = config('fulfillment.pickup.start_time');
        $endTime = config('fulfillment.pickup.end_time');

        $pickupAt = $this->pickupDateTime($pickupDate, $pickupTime);

        $startAt = $this->pickupDateTime($pickupDate, $startTime);

        $endAt = $this->pickupDateTime($pickupDate, $endTime);

        if ($pickupAt->lt($startAt) || $pickupAt->gt($endAt)) {
            throw ValidationException::withMessages([
                $field => 'Waktu pickup harus antara 09:00 dan 17:00.',
            ]);
        }

        $breakStartTime = config('fulfillment.pickup.break_start_time');
        $breakEndTime = config('fulfillment.pickup.break_end_time');

        if ($breakStartTime && $breakEndTime) {
            $breakStartAt = $this->pickupDateTime($pickupDate, (string) $breakStartTime);

            $breakEndAt = $this->pickupDateTime($pickupDate, (string) $breakEndTime);

            if ($pickupAt->gte($breakStartAt) && $pickupAt->lt($breakEndAt)) {
                throw ValidationException::withMessages([
                    $field => sprintf(
                        'Waktu pickup pada jam istirahat %s-%s WITA tidak tersedia. Silakan pilih sebelum pukul %s atau mulai pukul %s.',
                        $breakStartAt->format('H:i'),
                        $breakEndAt->copy()->subMinute()->format('H:i'),
                        $breakStartAt->format('H:i'),
                        $breakEndAt->format('H:i')
                    ),
                ]);
            }
        }

        if ($pickupDate->isSameDay($checkoutAt) && $pickupAt->lt($checkoutAt)) {
            throw ValidationException::withMessages([
                $field => 'Waktu pickup tidak boleh lebih awal dari waktu checkout.',
            ]);
        }
    }

    private function pickupDateTime(Carbon $pickupDate, string $time): Carbon
    {
        return Carbon::parse(
            $pickupDate->toDateString() . ' ' . $time,
            config('app.timezone')
        );
    }
    public function determine(
        string $shippingMethod,
        Carbon $checkoutAt,
        ?string $pickupDate = null,
    ): Carbon {
        if (! in_array($shippingMethod, ['Kurir', 'Ambil di Tempat'], true)) {
            throw ValidationException::withMessages([
                'shipping_method' => 'Metode pengiriman tidak valid.',
            ]);
        }

        $checkoutAt = $checkoutAt->copy();

        if ($shippingMethod === 'Kurir') {
            if ($this->isSpecialBatchApplicable($checkoutAt)) {
                return $this->determineForCourier();
            }

            return $checkoutAt->copy()->startOfDay();
        }

        if (! $pickupDate) {
            throw ValidationException::withMessages([
                'pickup_date' => 'Tanggal pickup wajib dipilih.',
            ]);
        }

        $pickupDate = Carbon::parse($pickupDate)->startOfDay();

        if ($this->slotService->isSpecialBatchDate($pickupDate)) {
            return $this->determineForPickup($pickupDate);
        }

        $expectedDate = $this->determineForNormalPickup($checkoutAt);

        if ($pickupDate->isBefore($expectedDate)) {
            throw ValidationException::withMessages([
                'pickup_date' => sprintf(
                    'Tanggal pickup tidak boleh sebelum %s.',
                    $expectedDate->format('d-m-Y')
                ),
            ]);
        }

        return $pickupDate;
    }
}