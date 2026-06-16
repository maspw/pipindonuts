<?php

namespace App\Http\Controllers;

use App\Models\DetilPenjualan;
use App\Models\Karyawan;
use App\Models\PenjualanProduk;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Phpml\Association\Apriori;

class KasirController extends Controller
{
    /**
     * Halaman utama POS kasir.
     */
    public function index()
    {
        $produk = Produk::where('stok', '>', 0)->orderBy('nama_produk')->get();
        
        // Ambil association rules (cache 1 jam)
        $rules = Cache::remember('association_rules', 3600, function() {
            return $this->getAssociationRules();
        });
        
        return view('kasir.index', compact('produk', 'rules'));
    }

    /**
     * Ambil association rules dari data transaksi.
     */
    private function getAssociationRules()
    {
        // Ambil data transaksi dari database dengan informasi produk lengkap
        $data = DB::table('penjualan_produks')
            ->join('detil_penjualans', 'penjualan_produks.id_penjualan', '=', 'detil_penjualans.id_penjualan')
            ->join('produk', 'detil_penjualans.produk_id', '=', 'produk.id_produk')
            ->select(
                'penjualan_produks.id_penjualan as transaksi_id',
                'produk.nama_produk',
                'produk.id_produk',
                'produk.harga',
                'produk.gambar',
                'produk.stok'
            )
            ->orderBy('penjualan_produks.id_penjualan')
            ->get();

        // Kelompokkan produk per transaksi
        $transaksi = [];
        $produkMap = [];

        foreach ($data as $row) {
            $transaksi[$row->transaksi_id][] = $row->nama_produk;
            $produkMap[$row->nama_produk] = [
                'id_produk' => $row->id_produk,
                'nama_produk' => $row->nama_produk,
                'harga' => $row->harga,
                'gambar' => $row->gambar,
                'stok' => $row->stok,
            ];
        }

        $samples = array_values($transaksi);

        // Filter transaksi dengan minimal 2 produk
        $samples = array_filter($samples, function($items) {
            return count($items) > 1;
        });

        $samples = array_values($samples);

        // Jika tidak ada data, return kosong
        if (empty($samples)) {
            return [];
        }

        // Training Apriori
        $associator = new Apriori(0.1, 0.3);
        $associator->train($samples, []);

        $rules = $associator->getRules();
        $result = [];

        foreach ($rules as $rule) {
            // $rule adalah array, bukan object!
            $antecedent = $rule['antecedent'] ?? [];
            $consequent = $rule['consequent'] ?? [];

            $result[] = [
                'antecedent' => $antecedent,
                'consequent' => $consequent,
                'confidence' => $rule['confidence'] ?? 0,
                'support' => $rule['support'] ?? 0,
                'lift' => $rule['lift'] ?? 0,
                'consequent_products' => array_map(function($name) use ($produkMap) {
                    return $produkMap[$name] ?? null;
                }, $consequent),
            ];
        }

        return $result;
    }

    /**
     * Proses transaksi dari form POS.
     */
    public function prosesTransaksi(Request $request)
    {
        $request->validate([
            'cart'         => 'required|json',
            'metode_bayar' => 'required|in:tunai,transfer,qris',
            'jumlah_bayar' => 'required|numeric|min:0',
        ]);

        $cart = json_decode($request->cart, true);

        if (empty($cart)) {
            return back()->with('error', 'Keranjang kosong!');
        }

        $karyawanId = auth()->user()->karyawan?->id_karyawan
                    ?? auth()->user()->name
                    ?? 'kasir';

        DB::beginTransaction();
        try {
            $total = collect($cart)->sum(fn ($i) => $i['harga'] * $i['qty']);

            // ── 1. Simpan penjualan ──────────────────────────────
            $penjualan = PenjualanProduk::create([
                'karyawan_id' => $karyawanId,
                'tgl_jual'    => today(),
                'total_jual'  => $total,
            ]);

            // ── 2. Simpan detail item ────────────────────────────
            foreach ($cart as $item) {
                DetilPenjualan::create([
                    'id_penjualan' => $penjualan->id_penjualan,
                    'produk_id'    => $item['id'],
                    'jumlah'       => $item['qty'],
                    'harga_satuan' => $item['harga'],
                    'sub_total'    => $item['harga'] * $item['qty'],
                ]);

                Produk::where('id_produk', $item['id'])
                    ->decrement('stok', $item['qty']);
            }

            // ── 3. Simpan pembayaran (tabel terpisah) ───────────
            \App\Models\Pembayaran::create([
                'id_penjualan' => $penjualan->id_penjualan,
                'metode_bayar' => $request->metode_bayar,
                'total_bayar'  => $request->jumlah_bayar,
                'kembalian'    => max(0, $request->jumlah_bayar - $total),
                'status_bayar' => 'lunas',
            ]);

            DB::commit();

            // Hapus cache rules setelah transaksi baru
            Cache::forget('association_rules');

            return redirect()->route('kasir.struk', $penjualan->id_penjualan);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transaksi gagal: ' . $e->getMessage());
        }
    }


    /**
     * Tampilkan struk setelah transaksi berhasil.
     */
    public function struk($id)
    {
        $penjualan = PenjualanProduk::with(['detil.produk', 'karyawan', 'pembayaran'])->findOrFail($id);
        return view('kasir.struk', compact('penjualan'));
    }

    /**
     * Buat Midtrans Snap token untuk pembayaran QRIS.
     */
    public function midtransToken(Request $request)
    {
        $request->validate(['total' => 'required|numeric|min:1']);

        $serverKey = env('MIDTRANS_SERVER_KEY');
        $orderId   = 'PIP-' . now()->format('YmdHis') . '-' . auth()->id();

        $payload = [
            'transaction_details' => [
                'order_id'    => $orderId,
                'gross_amount'=> (int) $request->total,
            ],
            'enabled_payments' => ['qris', 'gopay', 'bank_transfer'],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email'      => auth()->user()->email,
            ],
        ];

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->post('https://app.sandbox.midtrans.com/snap/v1/transactions', $payload);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'token'   => $response->json('token'),
                    'redirect_url' => $response->json('redirect_url'),
                    'order_id' => $orderId,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response->json('error_messages.0') ?? 'Gagal membuat transaksi Midtrans.',
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Refresh association rules (manual).
     */
    public function refreshRules()
    {
        Cache::forget('association_rules');
        return redirect()->back()->with('success', 'Rules berhasil di-refresh!');
    }
}