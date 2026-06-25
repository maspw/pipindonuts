<?php

namespace App\Observers;

use App\Models\PenjualanProduk;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Coa;

class PenjualanObserver
{
    /**
     * Handle the PenjualanProduk "created" event.
     */
    public function created(PenjualanProduk $penjualan): void
    {
        // =========================
        // 1. GENERATE NOMOR REFERENSI
        // =========================
        $lastRef = Jurnal::where('no_referensi', 'LIKE', 'F0004-%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = $lastRef ? intval(substr($lastRef->no_referensi, 6)) + 1 : 1;
        $refCode = 'F0004-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        $baseRef = $refCode;

        // =========================
        // 2. AMBIL COA
        // =========================
        $kas        = Coa::where('kode_akun', '211')->first();
        $pendapatan = Coa::where('kode_akun', '411')->first();

        // Jika COA tidak ditemukan, hentikan proses
        if (!$kas || !$pendapatan) {
            return;
        }

        // =========================
        // 3. HEADER JURNAL
        // =========================
        $jurnal = Jurnal::create([
            'tgl'          => $penjualan->tgl_jual,
            'no_referensi' => $refCode,
            'deskripsi'    => 'Penjualan Produk #' . $penjualan->id_penjualan,
        ]);

        // =========================
        // 4. DETAIL DEBIT (KAS)
        // =========================
        JurnalDetail::create([
            'jurnal_id'    => $jurnal->id,
            'coa_id'       => $kas->id,
            'debit'        => $penjualan->total_jual,
            'credit'       => 0,
            'no_referensi' => $baseRef . '-' . $kas->kode_akun,
        ]);

        // =========================
        // 5. DETAIL KREDIT (PENDAPATAN)
        // =========================
        JurnalDetail::create([
            'jurnal_id'    => $jurnal->id,
            'coa_id'       => $pendapatan->id,
            'debit'        => 0,
            'credit'       => $penjualan->total_jual,
            'no_referensi' => $baseRef . '-' . $pendapatan->kode_akun,
        ]);
    }
}