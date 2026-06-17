<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class ProduksiInsightWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;
    protected int | string | array $columnSpan = 'full';

    protected $listeners = [
        'refreshWidget' => '$refresh',
    ];

    protected function getStats(): array
    {
        $produksiInsight = session('ai_produksi_insight') ?? 'Silakan klik tombol "Generate Produksi AI Insight" di atas untuk merumuskan efisiensi jadwal kerja mesin dan optimasi adonan Pipindonuts 2026.';
        $statusJudul = session()->has('ai_produksi_insight') ? 'Analisis Kontrol Produksi Aktif' : 'Menunggu Formulasi Produksi';

        $cleanDisplay = new HtmlString("
            <div style='display: block; width: 100%; font-size: 0.925rem; line-height: 1.7; color: #9CA3AF; text-align: left; padding-top: 10px; white-space: normal; word-break: break-word;'>
                " . nl2br(e($produksiInsight)) . "
            </div>
        ");

        return [
            Stat::make('🥣 AI Production Output Insight 2026', $statusJudul)
                ->description($cleanDisplay)
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('info')
                ->extraAttributes([
                    'class' => 'col-span-full w-full block',
                    'style' => 'width: 100%; min-width: 100%; display: block;'
                ]),
        ];
    }
}