<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Lepas FK di pembelian_bahanbaku yang menunjuk ke suppliers ─────
        Schema::disableForeignKeyConstraints();

        // ── 2. Tambah kolom sementara untuk menampung ID baru (string) ────────
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('id_supplier_str', 20)->nullable()->after('id_supplier');
        });

        // ── 3. Konversi data lama: 1 → SPL-001, 2 → SPL-002, dst. ────────────
        $suppliers = DB::table('suppliers')->orderBy('id_supplier')->get();
        foreach ($suppliers as $s) {
            DB::table('suppliers')
                ->where('id_supplier', $s->id_supplier)
                ->update([
                    'id_supplier_str' => 'SPL-' . str_pad($s->id_supplier, 3, '0', STR_PAD_LEFT),
                ]);
        }

        // ── 4. Hapus auto_increment dulu (MySQL requirement) lalu drop PK ─────
        //    Tidak bisa dropPrimary() langsung pada kolom auto_increment
        DB::statement('ALTER TABLE suppliers MODIFY id_supplier BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE suppliers DROP PRIMARY KEY');

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('id_supplier');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->renameColumn('id_supplier_str', 'id_supplier');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('id_supplier', 20)->primary()->change();
        });

        // ── 5. Fix pembelian_bahanbaku: ganti supplier_id (bigint) → id_supplier (string) ──
        //    Tabel ini kosong, jadi aman langsung drop dan tambah ulang
        if (Schema::hasColumn('pembelian_bahanbaku', 'supplier_id')) {
            Schema::table('pembelian_bahanbaku', function (Blueprint $table) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            });
        }

        Schema::table('pembelian_bahanbaku', function (Blueprint $table) {
            $table->string('id_supplier', 20)->nullable()->after('id_pembelian');
            $table->foreign('id_supplier')
                  ->references('id_supplier')
                  ->on('suppliers')
                  ->onDelete('set null');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        // Balik ke bigint autoincrement (tidak bisa otomatis kembalikan data)
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropPrimary();
            $table->dropColumn('id_supplier');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->bigIncrements('id_supplier')->first();
        });

        Schema::enableForeignKeyConstraints();
    }
};
