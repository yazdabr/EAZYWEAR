<?php

namespace Database\Seeders;

use App\Models\FulfillmentSlot;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FulfillmentSlotSeeder extends Seeder
{
    /**
     * Seed the fulfillment slots for the special batch.
     */
    public function run(): void
    {
        $startDate = Carbon::parse(
            config('fulfillment.special_batch.start_date')
        );

        $endDate = Carbon::parse(
            config('fulfillment.special_batch.end_date')
        );

        $capacity = (int) config(
            'fulfillment.special_batch.daily_capacity'
        );

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            FulfillmentSlot::updateOrCreate(
                [
                    'date' => $date->toDateString(),
                ],
                [
                    'capacity' => $capacity,
                ]
            );
        }
    }
}