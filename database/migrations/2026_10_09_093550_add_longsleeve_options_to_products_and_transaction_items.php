
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('longsleeve_enabled')
                ->default(false)
                ->after('customization_price');

            $table->unsignedBigInteger('longsleeve_price')
                ->default(0)
                ->after('longsleeve_enabled');
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->boolean('is_longsleeve')
                ->default(false)
                ->after('custom_number');

            $table->unsignedBigInteger('longsleeve_price')
                ->default(0)
                ->after('is_longsleeve');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn([
                'is_longsleeve',
                'longsleeve_price',
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'longsleeve_enabled',
                'longsleeve_price',
            ]);
        });
    }
};
