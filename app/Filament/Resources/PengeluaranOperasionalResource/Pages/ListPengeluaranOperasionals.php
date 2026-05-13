<?php

namespace App\Filament\Resources\PengeluaranOperasionalResource\Pages;

use App\Filament\Resources\PengeluaranOperasionalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengeluaranOperasionals extends ListRecords
{
    protected static string $resource = PengeluaranOperasionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
