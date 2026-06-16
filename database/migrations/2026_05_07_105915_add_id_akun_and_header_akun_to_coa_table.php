<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coa', function (Blueprint $table) {

            $table->string('id_akun')->nullable();

            $table->string('header_akun')->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('coa', function (Blueprint $table) {

            $table->dropColumn(['id_akun', 'header_akun']);

        });
    }
};