<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Matikan FK check dulu karena ada relasi antar tabel
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('pembayarans');
        Schema::dropIfExists('detil_penjualans');
        Schema::dropIfExists('penjualan_produks');

        // ── 1. Recreate penjualan_produks (PK string) ─────────────
        Schema::create('penjualan_produks', function (Blueprint $table) {
            $table->string('id_penjualan', 20)->primary();
            $table->string('karyawan_id')->nullable();
            $table->date('tgl_jual');
            $table->bigInteger('total_jual');
            $table->timestamps();
        });

        // ── 2. Recreate detil_penjualans ───────────────────────────
        Schema::create('detil_penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('id_penjualan', 20);
            $table->unsignedBigInteger('produk_id');
            $table->integer('jumlah');
            $table->bigInteger('harga_satuan');
            $table->bigInteger('sub_total');
            $table->timestamps();

            $table->foreign('id_penjualan')
                ->references('id_penjualan')
                ->on('penjualan_produks')
                ->onDelete('cascade');
        });

        // ── 3. Recreate pembayarans ────────────────────────────────
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->string('id_pembayaran', 20)->primary();
            $table->string('id_penjualan', 20);
            $table->string('metode_bayar')->default('tunai');
            $table->bigInteger('total_bayar');
            $table->bigInteger('kembalian')->default(0);
            $table->string('status_bayar')->default('lunas');
            $table->timestamps();

            $table->foreign('id_penjualan')
                ->references('id_penjualan')
                ->on('penjualan_produks')
                ->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pembayarans');
        Schema::dropIfExists('detil_penjualans');
        Schema::dropIfExists('penjualan_produks');
        Schema::enableForeignKeyConstraints();
    }
};
