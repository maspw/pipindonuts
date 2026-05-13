<?php

namespace App\Observers;

use App\Models\DetilPembelian;

class DetilPembelianObserver
{
    /**
     * Saat detail pembelian dibuat → tambah stok bahan.
     */
    public function created(DetilPembelian $detil): void
    {
        $detil->bahan()->increment('jml_stok', $detil->jumlah);
    }

    /**
     * Saat detail pembelian diupdate → koreksi selisih stok.
     */
    public function updated(DetilPembelian $detil): void
    {
        $selisih = $detil->jumlah - $detil->getOriginal('jumlah');

        if ($selisih > 0) {
            $detil->bahan()->increment('jml_stok', $selisih);
        } elseif ($selisih < 0) {
            $detil->bahan()->decrement('jml_stok', abs($selisih));
        }
    }

    /**
     * Saat detail pembelian dihapus → kurangi stok bahan.
     */
    public function deleted(DetilPembelian $detil): void
    {
        $detil->bahan()->decrement('jml_stok', $detil->jumlah);
    }
}
