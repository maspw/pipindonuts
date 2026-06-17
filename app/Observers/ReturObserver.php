<?php

namespace App\Observers;

use App\Models\ReturPembelian;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Coa;

class ReturObserver
{
    public function created(ReturPembelian $retur): void
    {
        $lastRef = Jurnal::where('no_referensi', 'LIKE', 'F0006-%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = $lastRef ? intval(substr($lastRef->no_referensi, 6)) + 1 : 1;
        $refCode = 'F0006-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        $baseRef = $refCode;

        $kas = Coa::where('kode_akun', '211')->first();
        $persediaan = Coa::where('kode_akun', '114')->first();

        $nominal = $retur->jumlah * 15000;

        $jurnal = Jurnal::create([
            'tgl' => $retur->tgl_retur,
            'no_referensi' => $refCode,
            'deskripsi' => 'Retur Pembelian #' . $retur->pembelian_id,
        ]);

        // DEBIT Kas
        JurnalDetail::create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $kas->id,
            'debit' => $nominal,
            'credit' => 0,
            'no_referensi' => $baseRef . '-' . $kas->kode_akun,
        ]);

        // CREDIT Persediaan
        JurnalDetail::create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $persediaan->id,
            'debit' => 0,
            'credit' => $nominal,
            'no_referensi' => $baseRef . '-' . $persediaan->kode_akun,
        ]);
    }
}