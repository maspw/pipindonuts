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
        Schema::create('pembelian_bahanbaku', function (Blueprint $table) {
           $table->string('id_pembelian')->primary(); 
            $table->string('id_supplier');
            $table->foreign('id_supplier')->references('id_supplier')->on('supplier');
            $table->string('id_karyawan');
            $table->foreign('id_karyawan')->references('id_karyawan')->on('karyawans'); 
            $table->date('tgl_beli');
            $table->decimal('total_beli', 15, 2)->default(0);
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian_bahanbaku');
    }
};