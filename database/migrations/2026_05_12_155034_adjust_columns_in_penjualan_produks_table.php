<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('penjualan_produks', function (Blueprint $table) {
        // Kita buat kolom lama jadi nullable agar tidak error saat simpan Repeater
        $table->unsignedBigInteger('id_produk')->nullable()->change();
        $table->integer('qty')->nullable()->change();
        $table->integer('harga_jual')->nullable()->change();
    });
}

};
