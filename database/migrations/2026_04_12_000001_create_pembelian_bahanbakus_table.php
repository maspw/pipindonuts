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

            // suppliers.id = bigint unsigned (bukan string id_supplier)
            $table->unsignedBigInteger('supplier_id');
            $table->string('id_karyawan');

            $table->date('tgl_beli');
            $table->decimal('total_beli', 15, 2)->default(0);
            $table->string('dokumen')->nullable();
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->foreign('id_karyawan')->references('id_karyawan')->on('karyawans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian_bahanbaku');
    }
};