<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian_bahanbaku', function (Blueprint $table) {
            $table->string('id_pembelian')->primary(); 
            
            // Kolom Foreign Key (Harus String karena id_supplier kita String)
            $table->string('id_supplier'); 
            $table->string('id_karyawan');
            
            $table->date('tgl_beli');
            $table->decimal('total_beli', 15, 2)->default(0);
            $table->string('dokumen')->nullable();
            $table->timestamps();

            // Relasi Manual (Gembok dan Kunci harus pas)
            $table->foreign('id_supplier')->references('id_supplier')->on('suppliers')->onDelete('cascade');
            $table->foreign('id_karyawan')->references('id_karyawan')->on('karyawans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian_bahanbaku');
    }
};