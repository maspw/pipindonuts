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
    Schema::create('detail_pembelian', function (Blueprint $table) {
        $table->id();
        $table->string('id_pembelian'); 
        $table->foreign('id_pembelian')
              ->references('id_pembelian') 
              ->on('pembelian_bahanbaku')  
              ->onDelete('cascade');
        $table->string('id_bahanbaku'); 
        $table->integer('jumlah');
        $table->decimal('harga_satuan', 15, 2);
        $table->decimal('subtotal', 15, 2);
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pembelian');
    }
};