<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopCustomerChart extends ChartWidget
{
    protected static ?string $heading = '👑 Top Customer';

    protected static ?int $sort = 3;

    public function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $customers = DB::table('penjualan_produks')
            ->join('pembayarans', 'penjualan_produks.id_penjualan', '=', 'pembayarans.id_penjualan')
            ->where('pembayarans.status_bayar', 'lunas')
            ->selectRaw('penjualan_produks.nama_pelanggan, SUM(pembayarans.total_bayar) as total_belanja')
            ->groupBy('penjualan_produks.nama_pelanggan')
            ->orderByDesc('total_belanja')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Belanja',
                    'data' => $customers->pluck('total_belanja'),

                    // warna pink pastel aesthetic
                    'backgroundColor' => [
                        '#FFB7B2',
                        '#FFC8DD',
                        '#FFAFCC',
                        '#BDE0FE',
                        '#A2D2FF',
                    ],

                    'borderRadius' => 10,
                ],
            ],

            'labels' => $customers->pluck('nama_pelanggan'),
        ];
    }
}