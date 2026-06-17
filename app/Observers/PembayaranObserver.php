<?php

namespace App\Observers;

use App\Models\Pembayaran;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Helpers\CoaHelper;

class PembayaranObserver
{
    public function created(Pembayaran $pembayaran): void
    {
        $lastRef = Jurnal::where('no_referensi', 'LIKE', 'F0001-%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = $lastRef ? intval(substr($lastRef->no_referensi, 6)) + 1 : 1;
        $refCode = 'F0001-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        $kas = CoaHelper::get('211');
        $pendapatan = CoaHelper::get('411');

        $tanggal = $pembayaran->created_at ?? now();

        // HEADER
        $jurnal = Jurnal::create([
            'tgl' => $tanggal,
            'no_referensi' => $refCode,
            'deskripsi' => 'Penerimaan Kas Penjualan (' . $pembayaran->id_pembayaran . ')',
        ]);

        // DEBIT: Kas
        JurnalDetail::create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $kas->id,
            'debit' => $pembayaran->total_bayar,
            'credit' => 0,
        ]);

        // CREDIT: Pendapatan / Piutang
        JurnalDetail::create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $pendapatan->id,
            'debit' => 0,
            'credit' => $pembayaran->total_bayar,
        ]);
    }
}