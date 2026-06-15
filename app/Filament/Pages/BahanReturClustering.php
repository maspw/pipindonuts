<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Phpml\Clustering\KMeans;
use Phpml\Preprocessing\Normalizer;

class BahanReturClustering extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Clustering Bahan Retur';
    protected static ?string $navigationGroup = 'Analitik';
    protected static string  $view            = 'filament.pages.bahan-retur-clustering';

    /** @var array<int, array<array{x: int, y: int, name: string}>> */
    public array $clusterData  = [[], [], []];

    /** @var array<int, string> Label risiko per cluster index setelah di-sort */
    public array $clusterLabels = [];

    public function mount(): void
    {
        // ── 1. Ambil data dari DB ──────────────────────────────────────
        $dataBahan = DB::table('bahans')
            ->leftJoin('retur_pembelians', 'bahans.id_bahanbaku', '=', 'retur_pembelians.bahan_id')
            ->select(
                'bahans.id_bahanbaku',
                'bahans.nama_bahan',
                'bahans.jml_stok',
                DB::raw('COALESCE(SUM(retur_pembelians.jumlah), 0) as total_retur')
            )
            ->groupBy('bahans.id_bahanbaku', 'bahans.nama_bahan', 'bahans.jml_stok')
            ->get();

        if ($dataBahan->isEmpty()) {
            return;
        }

        // ── 2. Bangun samples + simpan metadata asli per index ─────────
        $samples  = [];
        $metadata = []; // index → ['nama', 'jml_stok', 'total_retur']

        foreach ($dataBahan as $i => $bahan) {
            $samples[]  = [(float) $bahan->jml_stok, (float) $bahan->total_retur];
            $metadata[] = [
                'nama'        => $bahan->nama_bahan,
                'jml_stok'    => (int) $bahan->jml_stok,
                'total_retur' => (int) $bahan->total_retur,
            ];
        }

        // ── 3. Normalisasi (z-score) ───────────────────────────────────
        // Normalisasi dilakukan di COPY terpisah agar metadata asli tetap utuh
        $samplesNorm = $samples;
        $normalizer  = new Normalizer(Normalizer::NORM_STD);
        $normalizer->transform($samplesNorm);

        // ── 4. K-Means clustering ─────────────────────────────────────
        $k             = min(3, count($samplesNorm));
        $kmeans        = new KMeans($k);
        $clusterResult = $kmeans->cluster($samplesNorm);

        // ── 5. Petakan hasil cluster ke metadata asli ─────────────────
        // php-ai/php-ml mengembalikan array titik yang merupakan referensi
        // ke elemen $samplesNorm, sehingga kita bisa pakai array_search
        // dengan strict=false. Tapi lebih aman: buat lookup dari nilai norm.
        $normToIndex = [];
        foreach ($samplesNorm as $idx => $point) {
            // key unik: gabungkan nilai float sebagai string
            $normToIndex[implode('|', $point)] = $idx;
        }

        $rawOutput = array_fill(0, $k, []);

        foreach ($clusterResult as $clusterIdx => $points) {
            foreach ($points as $point) {
                $key = implode('|', $point);
                $originalIdx = $normToIndex[$key] ?? null;

                if ($originalIdx !== null) {
                    $meta = $metadata[$originalIdx];
                    $rawOutput[$clusterIdx][] = [
                        'x'    => $meta['jml_stok'],
                        'y'    => $meta['total_retur'],
                        'name' => $meta['nama'],
                    ];
                }
            }
        }

        // ── 6. Sort cluster berdasarkan rata-rata total_retur ─────────
        // Cluster dengan avg retur tertinggi = High Risk
        $clusterAvgRetur = [];
        foreach ($rawOutput as $cIdx => $points) {
            $avg = count($points) > 0
                ? array_sum(array_column($points, 'y')) / count($points)
                : 0;
            $clusterAvgRetur[$cIdx] = $avg;
        }
        arsort($clusterAvgRetur); // descending: index pertama = highest retur

        $riskLabels = ['🔴 High Risk', '🟡 Medium Risk', '🟢 Low Risk'];
        $output     = [];
        $labels     = [];

        foreach (array_keys($clusterAvgRetur) as $rankIdx => $originalCIdx) {
            $output[]  = $rawOutput[$originalCIdx];
            $labels[]  = $riskLabels[$rankIdx] ?? "Cluster $rankIdx";
        }

        // Pastikan selalu 3 slot (jika k < 3 karena data sedikit)
        while (count($output) < 3) {
            $output[] = [];
            $labels[] = $riskLabels[count($labels)] ?? '-';
        }

        $this->clusterData   = $output;
        $this->clusterLabels = $labels;
    }
}