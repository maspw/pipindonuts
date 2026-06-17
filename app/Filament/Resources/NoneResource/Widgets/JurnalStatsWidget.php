<?php

namespace App\Filament\Widgets;

use App\Models\JurnalDetail;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class JurnalStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalDebit = JurnalDetail::sum('debit');
        $totalKredit = JurnalDetail::sum('credit');
        $saldo = $totalDebit - $totalKredit;

        return [
            Stat::make('Total Debit', 'Rp ' . number_format($totalDebit, 0, ',', '.')),
            Stat::make('Total Kredit', 'Rp ' . number_format($totalKredit, 0, ',', '.')),
            Stat::make('Saldo', 'Rp ' . number_format($saldo, 0, ',', '.'))
                ->description($saldo == 0 ? 'Seimbang ✔' : 'Tidak Balance ⚠')
                ->color($saldo == 0 ? 'success' : 'danger'),
        ];
    }
}