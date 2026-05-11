<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian_bahanbaku', function (Blueprint $table) {
            $table->id(); // Ini menghasilkan BigInt Unsigned
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            
            // Pastikan karyawan_id tipenya SAMA dengan id_karyawan di tabel karyawans
            $table->string('karyawan_id'); 
            $table->foreign('karyawan_id')->references('id_karyawan')->on('karyawans');
            
            $table->date('tgl_beli');
            $table->bigInteger('total_beli')->default(0);
            $table->string('dokumen')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian_bahanbaku');
    }
};
