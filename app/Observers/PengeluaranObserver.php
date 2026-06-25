<?php

namespace App\Observers;

use App\Models\PengeluaranOperasional;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Coa;

class PengeluaranObserver
{
    /**
     * Handle the PengeluaranOperasional "created" event.
     */
    public function created(PengeluaranOperasional $pengeluaran): void
    {
        // =========================
        // 1. GENERATE REF HEADER
        // =========================
        $lastRef = Jurnal::where('no_referensi', 'LIKE', 'F0002-%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = $lastRef ? intval(substr($lastRef->no_referensi, 6)) + 1 : 1;
        $refCode = 'F0002-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        $baseRef = $refCode;

        // =========================
        // 2. AMBIL COA
        // =========================
        $kas   = Coa::where('kode_akun', '211')->first(); // Kas
        $beban = Coa::where('kode_akun', '611')->first(); // Beban Operasional

        if (!$kas || !$beban) {
            return;
        }

        // =========================
        // 3. HEADER JURNAL
        // =========================
        $jurnal = Jurnal::create([
            'tgl'          => $pengeluaran->tanggal,
            'no_referensi' => $refCode,
            'deskripsi'    => 'Pengeluaran Operasional - ' . $pengeluaran->nama_pengeluaran,
        ]);

        // =========================
        // 4. DETAIL DEBIT (BEBAN)
        // =========================
        JurnalDetail::create([
            'jurnal_id'    => $jurnal->id,
            'coa_id'       => $beban->id,
            'debit'        => $pengeluaran->nominal,
            'credit'       => 0,
            'no_referensi' => $baseRef . '-' . $beban->kode_akun,
        ]);

        // =========================
        // 5. DETAIL CREDIT (KAS)
        // =========================
        JurnalDetail::create([
            'jurnal_id'    => $jurnal->id,
            'coa_id'       => $kas->id,
            'debit'        => 0,
            'credit'       => $pengeluaran->nominal,
            'no_referensi' => $baseRef . '-' . $kas->kode_akun,
        ]);
    }
}