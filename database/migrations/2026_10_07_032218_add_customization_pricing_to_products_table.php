<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('customization_enabled')
                ->default(false)
                ->after('material');

            $table->unsignedBigInteger('customization_price')
                ->default(0)
                ->after('customization_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'customization_enabled',
                'customization_price',
            ]);
        });
    }
};