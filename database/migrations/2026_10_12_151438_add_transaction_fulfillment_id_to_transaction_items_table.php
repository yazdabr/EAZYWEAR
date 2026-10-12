<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->foreignId('transaction_fulfillment_id')
                ->nullable()
                ->after('transaction_id')
                ->constrained('transaction_fulfillments')
                ->nullOnDelete();

            $table->index(
                ['transaction_fulfillment_id', 'transaction_id'],
                'transaction_items_fulfillment_transaction_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropIndex('transaction_items_fulfillment_transaction_index');
            $table->dropConstrainedForeignId('transaction_fulfillment_id');
        });
    }
};
