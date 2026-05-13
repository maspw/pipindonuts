<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detail_bahan_produksi', function (Blueprint $table) {
            $table->id();
            
            // FK ke Produksi (Varchar 12 sesuai id_produksi kamu)
            $table->string('id_produksi', 12); 
            
            // FK ke Bahans (Wajib VARCHAR 20 agar SAMA PERSIS dengan id_bahanbaku di tabel bahans)
            $table->string('id_bahanbaku', 20); 
            
            $table->integer('jumlah_dipakai');
            $table->timestamps();
    
            // Deklarasi relasi ke tabel produksi
            $table->foreign('id_produksi')
                  ->references('id_produksi')
                  ->on('produksi')
                  ->onDelete('cascade');
    
            // Deklarasi relasi ke tabel bahans
            $table->foreign('id_bahanbaku')
                  ->references('id_bahanbaku') // Nama kolom di tabel bahans
                  ->on('bahans')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Samakan dengan nama tabel di atas
        Schema::dropIfExists('detail_bahan_produksi');
    }

    
};
