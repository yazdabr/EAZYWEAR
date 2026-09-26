<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_notifications', function (Blueprint $table) {

            $table->id();

            $table->foreignId('transaction_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
             * Jenis notifikasi.
             *
             * Contoh:
             * PAYMENT_INSTRUCTION
             * PAYMENT_CONFIRMATION
             * PAYMENT_EXPIRED
             * ORDER_CANCELLED
             */
            $table->string('type', 50);

            /*
             * Null berarti:
             * notification belum berhasil dikirim.
             */
            $table->timestamp('sent_at')
                ->nullable();

            $table->timestamps();


            /*
             * Idempotency protection.
             *
             * Satu transaksi hanya boleh
             * memiliki satu notification type.
             *
             * Contoh:
             *
             * transaction_id = 26
             * type = PAYMENT_CONFIRMATION
             *
             * tidak boleh dibuat dua kali.
             */
            $table->unique([
                'transaction_id',
                'type',
            ]);

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('transaction_notifications');
    }
};