<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Pastikan tabel transactions memiliki PRIMARY KEY
         * sebelum foreign key dibuat.
         */
        $transactionsPrimaryKey = DB::select("
            SELECT COUNT(*) AS total
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = 'transactions'
              AND CONSTRAINT_TYPE = 'PRIMARY KEY'
        ");

        if ((int) $transactionsPrimaryKey[0]->total === 0) {
            DB::statement(
                'ALTER TABLE `transactions` ADD PRIMARY KEY (`id`)'
            );
        }

        /*
         * Pastikan tabel transaction_items memiliki PRIMARY KEY.
         */
        $transactionItemsPrimaryKey = DB::select("
            SELECT COUNT(*) AS total
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = 'transaction_items'
              AND CONSTRAINT_TYPE = 'PRIMARY KEY'
        ");

        if ((int) $transactionItemsPrimaryKey[0]->total === 0) {
            DB::statement(
                'ALTER TABLE `transaction_items` ADD PRIMARY KEY (`id`)'
            );
        }

        /*
         * Tambahkan transaction_id hanya jika belum tersedia.
         */
        if (!Schema::hasColumn('stock_movements', 'transaction_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->unsignedBigInteger('transaction_id')
                    ->nullable()
                    ->after('inventory_id');
            });
        }

        /*
         * Tambahkan transaction_item_id hanya jika belum tersedia.
         */
        if (!Schema::hasColumn('stock_movements', 'transaction_item_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->unsignedBigInteger('transaction_item_id')
                    ->nullable()
                    ->after('transaction_id');
            });
        }

        /*
         * Tambahkan foreign key transaction_id jika belum tersedia.
         */
        $transactionForeignKey = DB::select("
            SELECT COUNT(*) AS total
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = 'stock_movements'
              AND CONSTRAINT_NAME = 'stock_movements_transaction_id_foreign'
        ");

        if ((int) $transactionForeignKey[0]->total === 0) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->foreign('transaction_id')
                    ->references('id')
                    ->on('transactions')
                    ->nullOnDelete();
            });
        }

        /*
         * Tambahkan foreign key transaction_item_id jika belum tersedia.
         */
        $transactionItemForeignKey = DB::select("
            SELECT COUNT(*) AS total
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = 'stock_movements'
              AND CONSTRAINT_NAME = 'stock_movements_transaction_item_id_foreign'
        ");

        if ((int) $transactionItemForeignKey[0]->total === 0) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->foreign('transaction_item_id')
                    ->references('id')
                    ->on('transaction_items')
                    ->nullOnDelete();
            });
        }

        /*
         * Tambahkan index gabungan jika belum tersedia.
         */
        $indexExists = DB::select("
            SELECT COUNT(*) AS total
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'stock_movements'
              AND INDEX_NAME = 'stock_movements_transaction_id_transaction_item_id_type_index'
        ");

        if ((int) $indexExists[0]->total === 0) {
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
    }

    public function down(): void
    {
        /*
         * Hapus foreign key jika tersedia.
         */
        $foreignKeys = [
            'stock_movements_transaction_id_foreign',
            'stock_movements_transaction_item_id_foreign',
        ];

        foreach ($foreignKeys as $foreignKey) {
            $exists = DB::select("
                SELECT COUNT(*) AS total
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'stock_movements'
                  AND CONSTRAINT_NAME = ?
            ", [$foreignKey]);

            if ((int) $exists[0]->total > 0) {
                DB::statement(
                    "ALTER TABLE `stock_movements` DROP FOREIGN KEY `{$foreignKey}`"
                );
            }
        }

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