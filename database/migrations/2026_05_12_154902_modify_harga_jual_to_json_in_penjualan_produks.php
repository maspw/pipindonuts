<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('penjualan_produks', function (Blueprint $table) {
        // Mengubah tipe data agar bisa menyimpan data array dari Repeater
        $table->json('harga_jual')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('penjualan_produks', function (Blueprint $table) {
        $table->integer('harga_jual')->nullable()->change();
    });
}
};


