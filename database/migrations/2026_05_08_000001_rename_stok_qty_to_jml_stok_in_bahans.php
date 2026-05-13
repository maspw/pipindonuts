<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bahans', function (Blueprint $table) {
            $table->renameColumn('stok_qty', 'jml_stok');
        });
    }

    public function down(): void
    {
        Schema::table('bahans', function (Blueprint $table) {
            $table->renameColumn('jml_stok', 'stok_qty');
        });
    }
};
