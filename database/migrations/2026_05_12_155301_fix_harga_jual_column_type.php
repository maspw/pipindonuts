<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
{
    Schema::table('penjualan_produks', function (Blueprint $table) {
        // Mengubah tipe data menjadi longText atau json agar bisa nampung data repeater
        $table->longText('harga_jual')->nullable()->change();
    });
}
};
