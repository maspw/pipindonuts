<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ProdukTerlarisChart extends ChartWidget
{
    protected static ?string $heading = '🍩 Produk Terlaris';

    public function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $data = DB::table('detil_penjualans')
            ->join('produk', 'detil_penjualans.produk_id', '=', 'produk.id_produk')
            ->selectRaw('produk.nama_produk, SUM(detil_penjualans.jumlah) as total')
            ->groupBy('produk.nama_produk')
            ->pluck('total', 'nama_produk');

        return [
            'datasets' => [
                [
                    'data' => $data->values(),
                ],
            ],

            'labels' => $data->keys(),
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