<?php

namespace App\Filament\Resources\ReturPembelianResource\Pages;

use App\Filament\Resources\ReturPembelianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReturPembelians extends ListRecords
{
    protected static string $resource = ReturPembelianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Catat Retur Baru'),

            \Filament\Actions\ActionGroup::make([
                Actions\Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    ->url(route('retur.export.pdf'), shouldOpenInNewTab: true),

                Actions\Action::make('export_csv')
                    ->label('Export Excel / CSV')
                    ->icon('heroicon-o-table-cells')
                    ->color('success')
                    ->url(route('retur.export.csv'), shouldOpenInNewTab: true),
            ])
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->button(),
        ];
    }
}
