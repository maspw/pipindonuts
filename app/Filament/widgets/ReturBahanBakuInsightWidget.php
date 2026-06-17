<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class ReturBahanBakuInsightWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;
    protected int | string | array $columnSpan = 'full';

    protected $listeners = [
        'refreshWidget' => '$refresh',
    ];

    protected function getStats(): array
    {
        $returInsight = session('ai_retur_bahan_baku_insight') ?? 'Silakan klik tombol "Generate Retur AI Insight" untuk menganalisis penyebab retur bahan baku dan evaluasi supplier Pipindonuts 2026.';
        $statusJudul = session()->has('ai_retur_bahan_baku_insight') ? 'Analisis Quality Control Aktif' : 'Menunggu Formulasi QC';

        $cleanDisplay = new HtmlString("
            <div style='display: block; width: 100%; font-size: 0.925rem; line-height: 1.7; color: #9CA3AF; text-align: left; padding-top: 10px; white-space: normal; word-break: break-word;'>
                " . nl2br(e($returInsight)) . "
            </div>
        ");

        return [
            Stat::make('📦 AI Purchasing Return Insight 2026', $statusJudul)
                ->description($cleanDisplay)
                ->descriptionIcon('heroicon-m-archive-box-x-mark')
                ->color('danger') 
                ->extraAttributes([
                    'class' => 'col-span-full w-full block',
                    'style' => 'width: 100%; min-width: 100%; display: block;'
                ]),
        ];
    }
}