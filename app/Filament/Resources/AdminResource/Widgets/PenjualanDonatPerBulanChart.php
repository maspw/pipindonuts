<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PenjualanDonatPerBulanChart extends ChartWidget
{
    // Judul widget chart di dashboard
    protected static ?string $heading = '🍩 Penjualan Donat Per Bulan';

    // WAJIB DI FILAMENT V3: Menggantikan fungsi properti $type sebelumnya
    public function getType(): string
    {
        return 'line'; // Menggunakan grafik tipe garis (line chart)
    }

    protected function getData(): array
    {
        // Mengambil tahun saat ini (2026)
        $year = now()->year; 

        // Query mengambil total kuantitas donat yang terjual per bulan berdasarkan skema pipindonuts
        $dataPenjualan = DB::table('penjualan_produks')
            ->join('detil_penjualans', 'penjualan_produks.id_penjualan', '=', 'detil_penjualans.id_penjualan')
            ->join('produk', 'detil_penjualans.produk_id', '=', 'produk.id_produk')
            ->join('pembayarans', 'penjualan_produks.id_penjualan', '=', 'pembayarans.id_penjualan')
            ->where('pembayarans.status_bayar', 'lunas') // Filter hanya transaksi yang lunas
            ->where('produk.nama_produk', 'LIKE', '%Donut%') // Menyaring semua varian yang mengandung kata 'Donut'
            ->whereYear('penjualan_produks.tgl_jual', $year)
            ->selectRaw('MONTH(penjualan_produks.tgl_jual) as bulan, SUM(detil_penjualans.jumlah) as total_qty')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total_qty', 'bulan')
            ->toArray();

        // Menyusun data array untuk 12 bulan (Januari - Desember)
        $monthlyData = [];
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        for ($m = 1; $m <= 12; $m++) {
            // Jika data bulan kosong, otomatis diisi 0
            $monthlyData[] = $dataPenjualan[$m] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Donat Terjual (Pcs)',
                    'data' => $monthlyData,
                    // Menggunakan palet warna strawberry pink pastel yang imut
                    'borderColor' => '#FFB7B2', 
                    'backgroundColor' => 'rgba(255, 183, 178, 0.3)', 
                    'fill' => true,
                    'tension' => 0.4, // Membuat lekukan garis menjadi smooth dan aesthetic
                ],
            ],
            'labels' => $labels,
        ];
    }
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'suggestedMax' => 100,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}