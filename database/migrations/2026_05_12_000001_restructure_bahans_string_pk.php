<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // ── 1. Ganti kolom bahan_id di detil_pembelian ─────────────
        if (Schema::hasTable('detil_pembelian') && Schema::hasColumn('detil_pembelian', 'bahan_id')) {
            Schema::table('detil_pembelian', function (Blueprint $table) {
                $table->dropColumn('bahan_id');
            });
            Schema::table('detil_pembelian', function (Blueprint $table) {
                $table->string('bahan_id', 20)->nullable()->after('pembelian_id');
            });
        }

        // ── 2. Ganti kolom bahan_id di retur_pembelians ────────────
        if (Schema::hasTable('retur_pembelians') && Schema::hasColumn('retur_pembelians', 'bahan_id')) {
            Schema::table('retur_pembelians', function (Blueprint $table) {
                $table->dropColumn('bahan_id');
            });
            Schema::table('retur_pembelians', function (Blueprint $table) {
                $table->string('bahan_id', 20)->nullable()->after('pembelian_id');
            });
        }

        // ── 3. Drop & recreate bahans dengan PK string ─────────────
        Schema::dropIfExists('bahans');

        Schema::create('bahans', function (Blueprint $table) {
            $table->string('id_bahanbaku', 20)->primary();
            $table->string('nama_bahan');
            $table->string('satuan');
            $table->integer('jml_stok')->default(0);
            $table->integer('stok_minimum')->default(0);
            $table->date('tgl_exp')->nullable();
            $table->timestamps();
        });

        // ── 4. Add FK ke bahans dari tabel turunan ─────────────────
        if (Schema::hasTable('detil_pembelian')) {
            Schema::table('detil_pembelian', function (Blueprint $table) {
                $table->foreign('bahan_id')
                    ->references('id_bahanbaku')
                    ->on('bahans')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('retur_pembelians')) {
            Schema::table('retur_pembelians', function (Blueprint $table) {
                $table->foreign('bahan_id')
                    ->references('id_bahanbaku')
                    ->on('bahans')
                    ->nullOnDelete();
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bahans');
        Schema::enableForeignKeyConstraints();
    }
};
