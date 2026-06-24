<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class PembayaranInsightWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;
    protected int | string | array $columnSpan = 'full';

    protected $listeners = [
        'refreshWidget' => '$refresh',
    ];

    protected function getStats(): array
    {
        $pembayaranInsight = session('ai_pembayaran_insight') ?? 'Silakan klik tombol "Generate Pembayaran AI Insight" di atas untuk menganalisis kesehatan perputaran kas keluar Pipindonuts 2026.';
        $statusJudul = session()->has('ai_pembayaran_insight') ? 'Analisis Arus Kas Aktif' : 'Menunggu Formulasi Kas';

        $cleanDisplay = new HtmlString("
            <div style='display: block; width: 100%; font-size: 0.925rem; line-height: 1.7; color: #9CA3AF; text-align: left; padding-top: 10px; white-space: normal; word-break: break-word;'>
                " . nl2br(e($pembayaranInsight)) . "
            </div>
        ");

        return [
            Stat::make('💸 AI Cash Flow & Treasury Insight 2026', $statusJudul)
                ->description($cleanDisplay)
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('success') 
                ->extraAttributes([
                    'class' => 'col-span-full w-full block',
                    'style' => 'width: 100%; min-width: 100%; display: block;'
                ]),
        ];
    }
}