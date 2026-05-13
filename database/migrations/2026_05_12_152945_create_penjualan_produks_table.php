<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    // Hapus tabel lama jika sisa error tadi masih ada di database
    Schema::dropIfExists('penjualan_produks');

    Schema::create('penjualan_produks', function (Blueprint $table) {
        $table->id();
        
        // 1. SESUAIKAN TIPE DATA (Ini kuncinya!)
        // Di SQL kamu id_karyawan adalah VARCHAR, maka di sini harus string
        $table->string('id_karyawan'); 
        // Di SQL kamu id_produk adalah BIGINT UNSIGNED, maka di sini harus unsignedBigInteger
        $table->unsignedBigInteger('id_produk');
        
        // 2. SETUP RELASI MANUAL
        // Merujuk ke tabel 'karyawans' kolom 'id_karyawan'
        $table->foreign('id_karyawan')->references('id_karyawan')->on('karyawans')->onDelete('cascade');
        // Merujuk ke tabel 'produk' (bukan produks) kolom 'id_produk'
        $table->foreign('id_produk')->references('id_produk')->on('produk')->onDelete('cascade');

        // 3. DATA TRANSAKSI
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
