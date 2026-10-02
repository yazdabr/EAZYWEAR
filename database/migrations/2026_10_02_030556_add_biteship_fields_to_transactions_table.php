<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('biteship_order_id', 100)
                ->nullable()
                ->after('shipping_method');

            $table->string('biteship_tracking_id', 100)
                ->nullable()
                ->after('biteship_order_id');

            $table->string('biteship_waybill_id', 100)
                ->nullable()
                ->after('biteship_tracking_id');

            $table->string('biteship_status', 50)
                ->nullable()
                ->after('biteship_waybill_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'biteship_order_id',
                'biteship_tracking_id',
                'biteship_waybill_id',
                'biteship_status',
            ]);
        });
    }
};