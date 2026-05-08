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
        ];
    }
}
