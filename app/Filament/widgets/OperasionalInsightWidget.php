<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class OperasionalInsightWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;
    protected int | string | array $columnSpan = 'full';

    protected $listeners = [
        'refreshWidget' => '$refresh',
    ];

    protected function getStats(): array
    {
        $operationalInsight = session('ai_operational_insight') ?? 'Silakan klik tombol "Generate Operasional AI Insight" di atas untuk memetakan efisiensi pengeluaran harian Pipindonuts 2026.';
        $statusJudul = session()->has('ai_operational_insight') ? 'Rekomendasi Akuntansi Aktif' : 'Menunggu Formulasi Biaya';

        $cleanDisplay = new HtmlString("
            <div style='display: block; width: 100%; font-size: 0.925rem; line-height: 1.6; color: #9CA3AF; text-align: left; padding-top: 8px; white-space: pre-line; word-break: break-word;'>
                " . nl2br($operationalInsight) . "
            </div>
        ");

        return [
            Stat::make('📉 AI Operational Expenditure Insight 2026', $statusJudul)
                ->description($cleanDisplay)
                ->descriptionIcon('heroicon-m-presentation-chart-line')
                ->color('warning')
                ->extraAttributes([
                    'class' => 'col-span-full w-full block',
                    'style' => 'width: 100%; min-width: 100%; display: block;'
                ]),
        ];
    }
}