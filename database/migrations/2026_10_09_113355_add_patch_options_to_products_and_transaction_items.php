<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('patch_enabled')
                ->default(false)
                ->after('longsleeve_price');

            $table->unsignedBigInteger('patch_price')
                ->default(0)
                ->after('patch_enabled');
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->boolean('is_patch')
                ->default(false)
                ->after('longsleeve_price');

            $table->unsignedBigInteger('patch_price')
                ->default(0)
                ->after('is_patch');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn([
                'is_patch',
                'patch_price',
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'patch_enabled',
                'patch_price',
            ]);
        });
    }
};
