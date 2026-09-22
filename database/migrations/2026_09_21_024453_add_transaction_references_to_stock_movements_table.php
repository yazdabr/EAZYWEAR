<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('stock_movements', 'transaction_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->unsignedBigInteger('transaction_id')
                    ->nullable()
                    ->after('inventory_id');
            });
        }

        if (!Schema::hasColumn('stock_movements', 'transaction_item_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->unsignedBigInteger('transaction_item_id')
                    ->nullable()
                    ->after('transaction_id');
            });
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions')
                ->nullOnDelete();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('transaction_item_id')
                ->references('id')
                ->on('transaction_items')
                ->nullOnDelete();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(
                [
                    'transaction_id',
                    'transaction_item_id',
                    'type',
                ],
                'stock_movements_transaction_id_transaction_item_id_type_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign([
                'transaction_id',
            ]);

            $table->dropForeign([
                'transaction_item_id',
            ]);

            $table->dropIndex(
                'stock_movements_transaction_id_transaction_item_id_type_index'
            );
        });

        if (Schema::hasColumn('stock_movements', 'transaction_item_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropColumn('transaction_item_id');
            });
        }

        if (Schema::hasColumn('stock_movements', 'transaction_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropColumn('transaction_id');
            });
        }
    }
};