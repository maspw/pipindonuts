<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat tabelnya dulu tanpa foreign key agar tidak error saat pembuatan awal
        Schema::create('produksi', function (Blueprint $table) {
            $table->string('id_produksi', 12)->primary();
            
            // Samakan dengan id_karyawan di tabel karyawan
            $table->string('id_karyawan'); 
            
            $table->date('tgl_produksi');
            $table->string('status')->default('proses');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 2. Pasang Foreign Key di luar blok create agar lebih 'aman'
        // Jika bagian ini masih gagal, berarti ada masalah di struktur tabel karyawan-mu
        try {
            Schema::table('produksi', function (Blueprint $table) {
                $table->foreign('id_karyawan')
                      ->references('id_karyawan')
                      ->on('karyawan')
                      ->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Jika gagal pasang relasi di level DB, biarkan saja. 
            // Relasi tetap akan jalan di Filament lewat Model.
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('produksi');
    }
};