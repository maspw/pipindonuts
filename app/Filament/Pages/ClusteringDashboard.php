<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Phpml\Clustering\KMeans;
use Phpml\Preprocessing\Normalizer;

class ClusteringDashboard extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Analitik Clustering';
    protected static ?string $navigationGroup = 'Analitik';
    protected static string  $view            = 'filament.pages.clustering-dashboard';

    public string $activeType    = 'penjualan';
    public array  $clusterData   = [[], [], []];
    public array  $clusterLabels = [];
    public array  $summary       = [];
    public string $xAxisLabel    = '';
    public string $yAxisLabel    = '';

    public function getTypes(): array //Daftar Mode Clustering
    {
        return [
            'penjualan'   => ['label' => 'Penjualan Produk',       'icon' => '🛒'],
            'pembelian'   => ['label' => 'Pembelian Bahan Baku',    'icon' => '🚚'],
            'pengeluaran' => ['label' => 'Pengeluaran Operasional', 'icon' => '💸'],
            'pembayaran'  => ['label' => 'Pola Pembayaran',         'icon' => '💳'],
            'produksi'    => ['label' => 'Produktivitas Produksi',  'icon' => '⚗️'],
            'retur'       => ['label' => 'Retur Bahan Baku',        'icon' => '🔄'],
        ];
    }
    // Entry Point & Reaktivitas
    public function mount(): void
    {
        $this->loadClustering();
    }

    public function switchType(string $type): void
    {
        $this->activeType = $type;
        $this->loadClustering();

        // Kirim data ke JS lewat dispatch agar chart bisa update
        $this->dispatch('clusterDataUpdated', [
            'clusterData'   => $this->clusterData,
            'clusterLabels' => $this->clusterLabels,
            'xAxisLabel'    => $this->xAxisLabel,
            'yAxisLabel'    => $this->yAxisLabel,
            'activeType'    => $this->activeType,
        ]);
    }

    private function loadClustering(): void // Router Internal
    {
        match ($this->activeType) {
            'penjualan'   => $this->clusterPenjualan(),
            'pembelian'   => $this->clusterPembelian(),
            'pengeluaran' => $this->clusterPengeluaran(),
            'pembayaran'  => $this->clusterPembayaran(),
            'produksi'    => $this->clusterProduksi(),
            'retur'       => $this->clusterRetur(),
            default       => null,
        };
    }

    // ── 1. Penjualan Produk ───────────────────────────────────────
    private function clusterPenjualan(): void
    {
        $this->xAxisLabel = 'Total Item Terjual';
        $this->yAxisLabel = 'Total Nilai Jual (Rp)';

        $data = DB::table('penjualan_produks as p')
            ->leftJoin('detil_penjualans as d', 'p.id_penjualan', '=', 'd.id_penjualan')
            ->select('p.id_penjualan', 'p.total_jual', DB::raw('COALESCE(SUM(d.jumlah),0) as total_item'))
            ->groupBy('p.id_penjualan', 'p.total_jual')
            ->get();

        [$s, $m] = $this->toSamples($data, 'id_penjualan', 'total_item', 'total_jual');
        $this->runAndAssign($s, $m, 'y', ['🔴 Transaksi Besar', '🟡 Transaksi Sedang', '🟢 Transaksi Kecil']);
    }

    // ── 2. Pembelian Bahan Baku ───────────────────────────────────
    private function clusterPembelian(): void
    {
        $this->xAxisLabel = 'Total Item Dibeli';
        $this->yAxisLabel = 'Total Nilai Pembelian (Rp)';

        $data = DB::table('pembelian_bahanbaku as pb')
            ->leftJoin('detil_pembelian as d', 'pb.id_pembelian', '=', 'd.pembelian_id')
            ->select('pb.id_pembelian', 'pb.total_beli', DB::raw('COALESCE(SUM(d.jumlah),0) as total_item'))
            ->groupBy('pb.id_pembelian', 'pb.total_beli')
            ->get();

        [$s, $m] = $this->toSamples($data, 'id_pembelian', 'total_item', 'total_beli');
        $this->runAndAssign($s, $m, 'y', ['🔴 Pembelian Boros', '🟡 Pembelian Normal', '🟢 Pembelian Hemat']);
    }

    // ── 3. Pengeluaran Operasional ────────────────────────────────
    private function clusterPengeluaran(): void
    {
        $this->xAxisLabel = 'Frekuensi Pengeluaran';
        $this->yAxisLabel = 'Total Nominal (Rp)';

        $data = DB::table('pengeluaran_operasionals')
            ->select('nama_pengeluaran', DB::raw('COUNT(*) as frekuensi'), DB::raw('SUM(nominal) as total_nominal'))
            ->groupBy('nama_pengeluaran')
            ->get();

        [$s, $m] = $this->toSamples($data, 'nama_pengeluaran', 'frekuensi', 'total_nominal');
        $this->runAndAssign($s, $m, 'y', ['🔴 Biaya Anomali', '🟡 Biaya Tinggi', '🟢 Biaya Normal']);
    }

    // ── 4. Pembayaran ─────────────────────────────────────────────
    private function clusterPembayaran(): void
    {
        $this->xAxisLabel = 'Total Bayar (Rp)';
        $this->yAxisLabel = 'Kembalian (Rp)';

        $data = DB::table('pembayarans')
            ->select(
                DB::raw("CONCAT(id_penjualan,' (',UPPER(metode_bayar),')') as nama"),
                'total_bayar',
                DB::raw('COALESCE(kembalian,0) as kembalian')
            )
            ->get();

        [$s, $m] = $this->toSamples($data, 'nama', 'total_bayar', 'kembalian');
        $this->runAndAssign($s, $m, 'y', ['🔴 Kembalian Besar', '🟡 Kembalian Sedang', '🟢 Bayar Pas']);
    }

    // ── 5. Produksi per Karyawan ──────────────────────────────────
    private function clusterProduksi(): void
    {
        $this->xAxisLabel = 'Total Produksi';
        $this->yAxisLabel = 'Total Selesai';

        $data = DB::table('produksi')
            ->join('karyawans', 'produksi.id_karyawan', '=', 'karyawans.id_karyawan')
            ->select(
                'karyawans.nama',
                DB::raw('COUNT(*) as total_produksi'),
                DB::raw("SUM(CASE WHEN produksi.status='selesai' THEN 1 ELSE 0 END) as total_selesai")
            )
            ->groupBy('produksi.id_karyawan', 'karyawans.nama')
            ->get();

        [$s, $m] = $this->toSamples($data, 'nama', 'total_produksi', 'total_selesai');
        $this->runAndAssign($s, $m, 'y', ['🔴 Produktivitas Tinggi', '🟡 Produktivitas Sedang', '🟢 Produktivitas Rendah']);
    }

    // ── 6. Retur Bahan Baku: jml_stok vs total_retur per bahan ───
    private function clusterRetur(): void
    {
        $this->xAxisLabel = 'Jumlah Stok Bahan';
        $this->yAxisLabel = 'Total Jumlah Retur';

        $data = DB::table('bahans')
            ->leftJoin('retur_pembelians', 'bahans.id_bahanbaku', '=', 'retur_pembelians.bahan_id')
            ->select(
                'bahans.nama_bahan',
                'bahans.jml_stok',
                DB::raw('COALESCE(SUM(retur_pembelians.jumlah), 0) as total_retur')
            )
            ->groupBy('bahans.id_bahanbaku', 'bahans.nama_bahan', 'bahans.jml_stok')
            ->get();

        [$s, $m] = $this->toSamples($data, 'nama_bahan', 'jml_stok', 'total_retur');
        $this->runAndAssign($s, $m, 'y', ['🔴 High Risk', '🟡 Medium Risk', '🟢 Low Risk']);
    }

    private function toSamples($collection, string $nameCol, string $xCol, string $yCol): array // Konversi ke Format ML
    {
        $samples  = [];
        $metadata = [];

        foreach ($collection as $row) {
            $samples[]  = [(float) $row->$xCol, (float) $row->$yCol];
            $metadata[] = [
                'name' => (string) $row->$nameCol,
                'x'    => (float) $row->$xCol,
                'y'    => (float) $row->$yCol,
            ];
        }

        return [$samples, $metadata];
    }

    private function runAndAssign(array $samples, array $metadata, string $sortBy, array $labels): void //Inti K-Means + Sorting
    {
        $this->clusterData   = [[], [], []];
        $this->clusterLabels = $labels;
        $this->summary       = array_map(fn($l) => ['label' => $l, 'count' => 0, 'avg_x' => 0, 'avg_y' => 0], $labels);

        if (count($samples) < 2) return;

        $samplesNorm = $samples;
        $normalizer  = new Normalizer(Normalizer::NORM_STD);
        $normalizer->transform($samplesNorm);

        $k             = min(3, count($samplesNorm));
        $kmeans        = new KMeans($k);
        $clusterResult = $kmeans->cluster($samplesNorm);

        // Float-string key lookup
        $normToIdx = [];
        foreach ($samplesNorm as $idx => $pt) {
            $normToIdx[implode('|', $pt)] = $idx;
        }

        $rawOutput = array_fill(0, $k, []);
        foreach ($clusterResult as $ci => $points) {
            foreach ($points as $pt) {
                $oi = $normToIdx[implode('|', $pt)] ?? null;
                if ($oi !== null) {
                    $rawOutput[$ci][] = [
                        'x'    => $metadata[$oi]['x'],
                        'y'    => $metadata[$oi]['y'],
                        'name' => $metadata[$oi]['name'],
                    ];
                }
            }
        }

        // Sort by avg sortBy DESC → index 0 = highest
        $avgVal = array_map(
            fn($pts) => count($pts) > 0 ? array_sum(array_column($pts, $sortBy)) / count($pts) : 0,
            $rawOutput
        );
        arsort($avgVal);

        $output  = [];
        $lblOut  = [];
        $summary = [];

        foreach (array_keys($avgVal) as $rank => $origCi) {
            $pts      = $rawOutput[$origCi];
            $output[] = $pts;
            $lblOut[] = $labels[$rank] ?? "Cluster $rank";
            $summary[] = [
                'label' => $labels[$rank] ?? "Cluster $rank",
                'count' => count($pts),
                'avg_x' => count($pts) > 0 ? round(array_sum(array_column($pts, 'x')) / count($pts), 1) : 0,
                'avg_y' => count($pts) > 0 ? round(array_sum(array_column($pts, 'y')) / count($pts), 0) : 0,
            ];
        }

        while (count($output) < 3) {
            $i        = count($output);
            $output[] = [];
            $lblOut[] = $labels[$i] ?? '-';
            $summary[] = ['label' => $labels[$i] ?? '-', 'count' => 0, 'avg_x' => 0, 'avg_y' => 0];
        }

        $this->clusterData   = $output;
        $this->clusterLabels = $lblOut;
        $this->summary       = $summary;
    }
}
