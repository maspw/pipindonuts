<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('detil_penjualans')) {
            Schema::create('detil_penjualans', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('penjualan_id');
                $table->unsignedBigInteger('produk_id');
                $table->integer('jumlah');
                $table->bigInteger('harga_satuan');
                $table->bigInteger('sub_total');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('detil_penjualans');
    }
};
