<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('transaction_id')
                ->nullable()
                ->after('inventory_id')
                ->constrained('transactions')
                ->nullOnDelete();

            $table->foreignId('transaction_item_id')
                ->nullable()
                ->after('transaction_id')
                ->constrained('transaction_items')
                ->nullOnDelete();

            $table->index([
                'transaction_id',
                'transaction_item_id',
                'type',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
            $table->dropForeign(['transaction_item_id']);

            $table->dropIndex([
                'stock_movements_transaction_id_transaction_item_id_type_index',
            ]);

            $table->dropColumn([
                'transaction_id',
                'transaction_item_id',
            ]);
        });
    }
};