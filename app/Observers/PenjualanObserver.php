<?php

namespace App\Observers;

use App\Models\PenjualanProduk;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Coa;

class PenjualanObserver
{
    public function created(PenjualanProduk $penjualan): void
    {
        $lastRef = Jurnal::where('no_referensi', 'LIKE', 'F0004-%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = $lastRef ? intval(substr($lastRef->no_referensi, 6)) + 1 : 1;
        $refCode = 'F0004-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        $baseRef = $refCode;

        $piutang = Coa::where('kode_akun', '112')->first();
        $pendapatan = Coa::where('kode_akun', '411')->first();

        $jurnal = Jurnal::create([
            'tgl' => $penjualan->tgl_jual,
            'no_referensi' => $refCode,
            'deskripsi' => 'Penjualan Produk #' . $penjualan->id_penjualan,
        ]);

        // DEBIT Piutang
        JurnalDetail::create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $piutang->id,
            'debit' => $penjualan->total_jual,
            'credit' => 0,
            'no_referensi' => $baseRef . '-' . $piutang->kode_akun,
        ]);

        // CREDIT Pendapatan
        JurnalDetail::create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $pendapatan->id,
            'debit' => 0,
            'credit' => $penjualan->total_jual,
            'no_referensi' => $baseRef . '-' . $pendapatan->kode_akun,
        ]);
    }
}