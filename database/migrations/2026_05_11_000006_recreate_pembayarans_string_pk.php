<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop dan recreate karena ganti tipe PK dari bigint ke string
        Schema::dropIfExists('pembayarans');

        Schema::create('pembayarans', function (Blueprint $table) {
            $table->string('id_pembayaran', 20)->primary();
            $table->unsignedBigInteger('penjualan_id');
            $table->string('metode_bayar')->default('tunai');
            $table->bigInteger('total_bayar');
            $table->bigInteger('kembalian')->default(0);
            $table->string('status_bayar')->default('lunas');
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
