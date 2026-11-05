<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_notifications', function (Blueprint $table) {
            $table->string('status', 20)->default('pending');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('recovery_attempts')->default(0);
            $table->timestamp('available_at')->nullable();
            $table->timestamp('locked_until')->nullable();
            $table->text('last_error')->nullable();

            $table->index(['status', 'available_at']);
        });
    }

    public function down(): void
    {
        Schema::table('transaction_notifications', function (Blueprint $table) {
            $table->dropIndex(['status', 'available_at']);

            $table->dropColumn([
                'status',
                'attempts',
                'recovery_attempts',
                'available_at',
                'locked_until',
                'last_error',
            ]);
        });
    }
};
