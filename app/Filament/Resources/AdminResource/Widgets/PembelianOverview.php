<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\PembelianBahanbaku;

class PembelianOverview extends BaseWidget
{
   
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $terakhir = PembelianBahanbaku::orderBy('created_at', 'desc')->first();
        $insight = $terakhir?->ai_insight ?? 'Belum ada data insight. Silakan klik tombol Refresh AI Insights.';
        $totalPengeluaran = PembelianBahanbaku::sum('total_beli');

        return [
        
            Stat::make('Total Pengeluaran Belanja', 'Rp ' . number_format($totalPengeluaran, 0, ',', '.'))
                ->description('Total keseluruhan dana keluar untuk pengadaan bahan baku.')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger'),

            
            Stat::make('Rekomendasi Strategis Pembelian (Gemini AI)', 'AI Insights')
                ->description($insight)
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('success')
                ->extraAttributes([
                    'class' => 'col-span-full whitespace-normal break-words',
                ]),
        ];
    }
}