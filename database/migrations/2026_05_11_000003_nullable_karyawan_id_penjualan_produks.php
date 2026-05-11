<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan_produks', function (Blueprint $table) {
            $table->string('karyawan_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('penjualan_produks', function (Blueprint $table) {
            $table->string('karyawan_id')->nullable(false)->change();
        });
    }
};
