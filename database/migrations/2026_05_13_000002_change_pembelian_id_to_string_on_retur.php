<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kosongkan data retur lama karena nyangkut dengan ID numeric yang sudah hilang di tabel pembelian_bahanbaku
        DB::table('retur_pembelians')->truncate();

        // Ubah pembelian_id menjadi string
        DB::statement('ALTER TABLE retur_pembelians MODIFY pembelian_id VARCHAR(20) NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE retur_pembelians MODIFY pembelian_id BIGINT UNSIGNED NOT NULL');
    }
};
