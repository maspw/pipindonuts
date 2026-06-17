<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelian_bahanbaku', function (Blueprint $table) {
            $table->text('ai_insight')->nullable(); 
        });
    }

    public function down(): void
    {
        Schema::table('pembelian_bahanbaku', function (Blueprint $table) {
            $table->dropColumn('ai_insight');
        });
    }
};