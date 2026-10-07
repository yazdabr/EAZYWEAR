<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fulfillment_holds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaction_id')
                ->unique()
                ->constrained();

            $table->foreignId('fulfillment_slot_id')
                ->constrained();

            $table->string('status', 20);

            $table->timestamp('released_at')->nullable();

            $table->timestamps();

            $table->index(
                ['fulfillment_slot_id', 'status'],
                'fulfillment_holds_slot_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fulfillment_holds');
    }
};