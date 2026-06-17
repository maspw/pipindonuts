<?php

namespace App\Filament\Exports;

use App\Models\PembelianBahanbaku;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PembelianBahanbakuExporter extends Exporter
{
    protected static ?string $model = PembelianBahanbaku::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('no_faktur')->label('No Faktur'),
            ExportColumn::make('tgl_beli')->label('Tanggal Beli'),
            ExportColumn::make('supplier.nama_supplier')->label('Supplier'),
            ExportColumn::make('total_beli')->label('Total Harga'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Ekspor selesai. ' . number_format($export->successful_rows) . ' data siap diunduh.';
    }
}