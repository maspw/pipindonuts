<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Phpml\Association\Apriori;

class AprioriTestController extends Controller
{
    public function test()
    {
        // 1. Ambil data transaksi dari database
        $data = DB::table('penjualan')
            ->join('detail_penjualan', 'penjualan.id', '=', 'detail_penjualan.penjualan_id')
            ->join('produk', 'detail_penjualan.produk_id', '=', 'produk.id')
            ->select(
                'penjualan.id as transaksi_id',
                'produk.nama_produk'
            )
            ->orderBy('penjualan.id')
            ->get();

        // 2. Kelompokkan produk per transaksi
        $transaksi = [];

        foreach ($data as $row) {
            $transaksi[$row->transaksi_id][] = $row->nama_produk;
        }

        // 3. Ubah ke array biasa
        $samples = array_values($transaksi);

        // 4. Filter transaksi yang hanya punya 1 produk (minimal 2 produk)
        $samples = array_filter($samples, function ($items) {
            return count($items) > 1;
        });

        // 5. Reset index array
        $samples = array_values($samples);

        // 6. Cek apakah ada data
        if (empty($samples)) {
            return "Tidak ada transaksi dengan minimal 2 produk!";
        }

        // 7. Inisialisasi Apriori
        $associator = new Apriori(
            0.1, // support (10%)
            0.3  // confidence (30%)
        );

        // 8. Latih model (tanpa labels)
        $associator->train($samples);

        // 9. Ambil rules
        $rules = $associator->getRules();

        // 10. Tampilkan hasil dengan view
        return view('apriori-test', [
            'rules' => $rules,
            'samples' => $samples,
            'total_transaksi' => count($samples)
        ]);
    }
}