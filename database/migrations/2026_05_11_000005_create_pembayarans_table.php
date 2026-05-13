<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id('id_pembayaran');
            $table->unsignedBigInteger('penjualan_id');
            $table->string('metode_bayar')->default('tunai'); // tunai, transfer, qris
            $table->bigInteger('total_bayar');
            $table->bigInteger('kembalian')->default(0);
            $table->string('status_bayar')->default('lunas'); // lunas, pending
            $table->timestamps();

            $table->foreign('penjualan_id')
                ->references('id')
                ->on('penjualan_produks')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
