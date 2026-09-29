<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('courier_code', 50)
                ->nullable()
                ->after('courier');

            $table->string('courier_service_code', 100)
                ->nullable()
                ->after('courier_code');

            $table->date('pickup_date')
                ->nullable()
                ->after('tracking_number');

            $table->time('pickup_time_start')
                ->nullable()
                ->after('pickup_date');

            $table->time('pickup_time_end')
                ->nullable()
                ->after('pickup_time_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'courier_code',
                'courier_service_code',
                'pickup_date',
                'pickup_time_start',
                'pickup_time_end',
            ]);
        });
    }
};