<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('detil_pembelian')) {
            Schema::create('detil_pembelian', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pembelian_id')->constrained('pembelian_bahanbaku')->onDelete('cascade');
                $table->foreignId('bahan_id')->constrained('bahans')->cascadeOnDelete();
                $table->integer('jumlah');
                $table->bigInteger('harga_satuan');
                $table->bigInteger('sub_total');
                $table->date('tgl_kadaluarsa')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('detil_pembelian');
    }
};
