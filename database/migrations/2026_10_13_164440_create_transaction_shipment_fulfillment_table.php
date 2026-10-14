<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_shipment_fulfillment', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaction_shipment_id');
            $table->foreign('transaction_shipment_id', 'tsf_shipment_fk')
                ->references('id')->on('transaction_shipments')->cascadeOnDelete();

            $table->foreignId('transaction_fulfillment_id');
            $table->foreign('transaction_fulfillment_id', 'tsf_fulfillment_fk')
                ->references('id')->on('transaction_fulfillments')->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['transaction_shipment_id', 'transaction_fulfillment_id'],
                'shipment_fulfillment_pair_unique'
            );

            $table->unique(
                'transaction_fulfillment_id',
                'shipment_fulfillment_fulfillment_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_shipment_fulfillment');
    }
};
