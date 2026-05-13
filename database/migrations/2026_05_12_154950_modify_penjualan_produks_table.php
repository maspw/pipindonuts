<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('penjualan_produks', function (Blueprint $table) {
        // Mengubah id_produk agar boleh kosong (nullable) karena data pindah ke detail_pesanan
        $table->unsignedBigInteger('id_produk')->nullable()->change();
        
        // Menambah kolom baru untuk menampung banyak rasa donat
        $table->json('detail_pesanan')->nullable();
    });
}
};
