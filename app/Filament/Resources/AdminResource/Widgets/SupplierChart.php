<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SupplierChart extends ChartWidget
{
    protected static ?string $heading = 'Supplier Pemasok Terbanyak';

    protected function getType(): string
    {
        return 'bar'; // bisa diganti pie / line
    }

    protected function getData(): array
    {
        
        $query = DB::table('pembelian_bahanbaku as pb')
            ->join('suppliers as s', 'pb.id_supplier', '=', 's.id_supplier')
            ->select('s.nama_supplier', DB::raw('COUNT(pb.id_pembelian) as total'))
            ->groupBy('s.nama_supplier')
            ->orderByDesc('total')
            ->get();

        // Ambil label & data
        $labels = $query->pluck('nama_supplier')->toArray();
        $data = $query->pluck('total')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Pembelian',
                    'data' => $data,
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