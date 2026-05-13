<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('retur_pembelians')) {
            Schema::create('retur_pembelians', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pembelian_id');
                $table->unsignedBigInteger('bahan_id');
                $table->string('karyawan_id');
                $table->string('tipe_retur');
                $table->integer('jumlah');
                $table->string('status')->default('pending');
                $table->text('alasan')->nullable();
                $table->date('tgl_retur');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('retur_pembelians');
    }
};
