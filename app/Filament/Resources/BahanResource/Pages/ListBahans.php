<?php

namespace App\Filament\Resources\BahanResource\Pages;

use App\Filament\Resources\BahanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBahans extends ListRecords
{
    protected static string $resource = BahanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
