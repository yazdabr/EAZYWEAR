<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_fulfillments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();

            $table->string('group_type', 20);
            $table->string('shipping_method', 30);
            $table->unsignedBigInteger('shipping_cost')->default(0);

            $table->date('fulfillment_date')->nullable();
            $table->date('pickup_date')->nullable();
            $table->time('pickup_time_start')->nullable();
            $table->time('pickup_time_end')->nullable();

            $table->string('shipping_name')->nullable();
            $table->string('shipping_email')->nullable();
            $table->string('shipping_phone', 30)->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_district')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_province')->nullable();
            $table->string('shipping_postal_code', 20)->nullable();
            $table->decimal('shipping_latitude', 10, 7)->nullable();
            $table->decimal('shipping_longitude', 10, 7)->nullable();

            $table->string('courier_code')->nullable();
            $table->string('courier_service_code')->nullable();

            $table->string('biteship_order_id')->nullable()->unique();
            $table->string('biteship_tracking_id')->nullable();
            $table->string('biteship_waybill_id')->nullable();
            $table->string('biteship_status')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('courier')->nullable();

            $table->string('status', 30)->default('PENDING');
            $table->timestamps();

            $table->unique(
                ['transaction_id', 'group_type'],
                'transaction_fulfillments_transaction_group_unique'
            );

            $table->index(
                ['transaction_id', 'status'],
                'transaction_fulfillments_transaction_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_fulfillments');
    }
};
