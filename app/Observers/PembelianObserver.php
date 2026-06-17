<?php

namespace App\Observers;

use App\Models\PembelianBahanbaku;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Coa;

class PembelianObserver
{
    public function created(PembelianBahanbaku $pembelian): void
    {
        // =========================
        // 1. GENERATE REF HEADER
        // =========================
        $lastRef = Jurnal::where('no_referensi', 'LIKE', 'F0003-%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = $lastRef ? intval(substr($lastRef->no_referensi, 6)) + 1 : 1;
        $refCode = 'F0003-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        $baseRef = $refCode;

        // =========================
        // 2. AMBIL COA
        // =========================
        $kas = Coa::where('kode_akun', '211')->first(); // Kas
        $persediaan = Coa::where('kode_akun', '114')->first(); // Persediaan

        // =========================
        // 3. HEADER JURNAL
        // =========================
        $jurnal = Jurnal::create([
            'tgl' => $pembelian->tgl_beli,
            'no_referensi' => $refCode,
            'deskripsi' => 'Pembelian Bahan Baku #' . $pembelian->id_pembelian,
        ]);

        // =========================
        // 4. DETAIL DEBIT (PERSEDIAAN)
        // =========================
        JurnalDetail::create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $persediaan->id,
            'debit' => $pembelian->total_beli,
            'credit' => 0,
            'no_referensi' => $baseRef . '-' . $persediaan->kode_akun,
        ]);

        // =========================
        // 5. DETAIL CREDIT (KAS)
        // =========================
        JurnalDetail::create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $kas->id,
            'debit' => 0,
            'credit' => $pembelian->total_beli,
            'no_referensi' => $baseRef . '-' . $kas->kode_akun,
        ]);
    }
}