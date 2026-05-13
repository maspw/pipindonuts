<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus kolom pembayaran dari penjualan_produks
        Schema::table('penjualan_produks', function (Blueprint $table) {
            $table->dropColumn(['metode_bayar', 'jumlah_bayar', 'kembalian', 'status_bayar']);
        });

        // Rename kolom agar sesuai ERD
        Schema::table('penjualan_produks', function (Blueprint $table) {
            $table->renameColumn('total', 'total_jual');
            $table->renameColumn('tgl_penjualan', 'tgl_jual');
        });
    }

    public function down(): void
    {
        Schema::table('penjualan_produks', function (Blueprint $table) {
            $table->renameColumn('total_jual', 'total');
            $table->renameColumn('tgl_jual', 'tgl_penjualan');
        });

        Schema::table('penjualan_produks', function (Blueprint $table) {
            $table->string('metode_bayar')->default('tunai');
            $table->bigInteger('jumlah_bayar')->default(0);
            $table->bigInteger('kembalian')->default(0);
            $table->string('status_bayar')->default('lunas');
        });
    }
};
