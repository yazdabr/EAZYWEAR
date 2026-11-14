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
    /**
     * Normalisasi rentang waktu pickup terhadap jam istirahat.
     *
     * Waktu selesai tepat pada akhir jam istirahat digeser ke awal jam
     * istirahat selama hasilnya masih membentuk rentang yang valid. Rentang
     * yang hanya melewati jam istirahat tetap dipertahankan, sedangkan waktu
     * mulai yang berada di dalam jam istirahat ditolak.
     *
     * @return array{0: string, 1: string}
     */
    public function normalizePickupTimeRange(
        string $pickupTimeStart,
        string $pickupTimeEnd,
        string $startField = 'pickup_time_start',
        string $endField = 'pickup_time_end'
    ): array {
        $breakStartTime = config('fulfillment.pickup.break_start_time');
        $breakEndTime = config('fulfillment.pickup.break_end_time');

        if ($breakStartTime && $breakEndTime) {
            $breakStartTime = (string) $breakStartTime;
            $breakEndTime = (string) $breakEndTime;

            if (
                $pickupTimeStart > $breakStartTime
                && $pickupTimeStart < $breakEndTime
            ) {
                throw ValidationException::withMessages([
                    $startField => sprintf(
                        'Waktu mulai pickup pukul %s berada dalam jam istirahat %s-%s WITA. Silakan sesuaikan waktu mulai pickup di luar jam istirahat.',
                        $pickupTimeStart,
                        $breakStartTime,
                        $breakEndTime
                    ),
                ]);
            }

            if ($pickupTimeEnd === $breakEndTime) {
                if ($breakStartTime > $pickupTimeStart) {
                    $pickupTimeEnd = $breakStartTime;
                } else {
                    throw ValidationException::withMessages([
                        $endField => sprintf(
                            'Waktu selesai pickup pukul %s berada pada akhir jam istirahat %s-%s WITA dan tidak dapat disesuaikan menjadi pukul %s karena harus setelah waktu mulai pickup (%s). Silakan pilih waktu selesai setelah pukul %s atau ubah waktu mulai pickup ke sebelum pukul %s.',
                            $pickupTimeEnd,
                            $breakStartTime,
                            $breakEndTime,
                            $breakStartTime,
                            $pickupTimeStart,
                            $breakEndTime,
                            $breakStartTime
                        ),
                    ]);
                }
            }
        }

        if ($pickupTimeStart >= $pickupTimeEnd) {
            throw ValidationException::withMessages([
                $endField => 'Waktu selesai pickup harus setelah waktu mulai pickup.',
            ]);
        }

        return [$pickupTimeStart, $pickupTimeEnd];
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
                $field => sprintf(
                    'Waktu pickup harus antara %s dan %s.',
                    $startAt->format('H:i'),
                    $endAt->format('H:i')
                ),
            ]);
        }

        // Aturan jam istirahat diterapkan pada rentang pickup melalui
        // normalizePickupTimeRange(), bukan pada masing-masing batas waktu.
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