<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migration ini sudah obsolete — tabel penjualan_produks sudah dikelola
        // oleh migration yang lebih baru. Skip jika tabel sudah ada.
        if (Schema::hasTable('penjualan_produks')) {
            return;
        }

        Schema::dropIfExists('penjualan_produks');

        Schema::create('penjualan_produks', function (Blueprint $table) {
            $table->id();
            $table->string('id_karyawan');
            $table->unsignedBigInteger('id_produk');
            $table->foreign('id_karyawan')->references('id_karyawan')->on('karyawans')->onDelete('cascade');
            $table->foreign('id_produk')->references('id_produk')->on('produk')->onDelete('cascade');
            $table->integer('qty')->default(1);
            $table->integer('harga_jual');
            $table->integer('total_jual');
            $table->dateTime('tgl_jual');
            $table->string('metode_pembayaran')->default('Tunai');
            $table->integer('uang_diterima')->nullable();
            $table->integer('uang_kembalian')->nullable();
            $table->timestamps();
        });
    }
};
