<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('penjualan_produks')) {
            Schema::create('penjualan_produks', function (Blueprint $table) {
                $table->id();
                $table->string('karyawan_id');
                $table->date('tgl_penjualan');
                $table->bigInteger('total');
                $table->string('metode_bayar')->default('tunai'); // tunai, transfer, qris
                $table->bigInteger('jumlah_bayar');
                $table->bigInteger('kembalian')->default(0);
                $table->string('status_bayar')->default('lunas');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan_produks');
    }
};
