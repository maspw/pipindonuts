<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengeluaran_operasionals', function (Blueprint $table) {

            $table->id();

            // ID Pengeluaran
            $table->string('id_pengeluaran')->unique();

            // ambil dari tabel karyawan
            $table->string('id_karyawan');

            // tanggal
            $table->date('tanggal');

            // nama pengeluaran
            $table->string('nama_pengeluaran');

            // nominal
            $table->bigInteger('nominal');

            // keterangan
            $table->text('keterangan')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran_operasionals');
    }
};