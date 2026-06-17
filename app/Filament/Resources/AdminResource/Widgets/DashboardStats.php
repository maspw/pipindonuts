<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(
                'Total Penjualan',
                'Rp ' . number_format(
                    DB::table('pembayarans')
                        ->where('status_bayar', 'lunas')
                        ->sum('total_bayar'),
                    0,
                    ',',
                    '.'
                )
            ),

            Stat::make(
                'Jumlah Produk',
                DB::table('produk')->count()
            ),

            Stat::make(
                'Total Pelanggan',
                DB::table('pelanggans')->count()
            ),

            Stat::make(
                'Transaksi Lunas',
                DB::table('pembayarans')
                    ->where('status_bayar', 'lunas')
                    ->count()
            ),
        ];
    }
}