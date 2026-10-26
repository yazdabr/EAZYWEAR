<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_items', function (Blueprint $table) {
            // Tambahkan index pengganti terlebih dahulu.
            $table->unique(
                ['production_record_id', 'size_id', 'is_longsleeve'],
                'production_size_type_unique'
            );
        });

        Schema::table('production_items', function (Blueprint $table) {
            // Hapus aturan unik lama setelah index baru tersedia.
            $table->dropUnique('production_size_unique');
        });
    }

    public function down(): void
    {
        Schema::table('production_items', function (Blueprint $table) {
            $table->dropUnique('production_size_type_unique');
        });

        Schema::table('production_items', function (Blueprint $table) {
            $table->unique(
                ['production_record_id', 'size_id'],
                'production_size_unique'
            );
        });
    }
};