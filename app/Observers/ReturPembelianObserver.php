<?php

namespace App\Observers;

use App\Models\ReturPembelian;

class ReturPembelianObserver
{
    /**
     * Saat retur dibuat langsung dengan status 'disetujui' → kurangi stok.
     */
    public function created(ReturPembelian $retur): void
    {
        if ($retur->status === 'disetujui') {
            $retur->bahan()->decrement('jml_stok', $retur->jumlah);
        }
    }

    /**
     * Saat status diupdate:
     * - pending/ditolak → disetujui : kurangi stok
     * - disetujui → pending/ditolak : kembalikan stok
     */
    public function updated(ReturPembelian $retur): void
    {
        $statusLama = $retur->getOriginal('status');
        $statusBaru = $retur->status;

        $wasApproved = $statusLama === 'disetujui';
        $isApproved  = $statusBaru === 'disetujui';

        if (!$wasApproved && $isApproved) {
            // Status berubah jadi disetujui → kurangi stok
            $retur->bahan()->decrement('jml_stok', $retur->jumlah);
        } elseif ($wasApproved && !$isApproved) {
            // Status berubah dari disetujui → kembalikan stok
            $retur->bahan()->increment('jml_stok', $retur->jumlah);
        }
    }

    /**
     * Saat retur dihapus dan statusnya disetujui → kembalikan stok.
     */
    public function deleted(ReturPembelian $retur): void
    {
        if ($retur->status === 'disetujui') {
            $retur->bahan()->increment('jml_stok', $retur->jumlah);
        }
    }
}
