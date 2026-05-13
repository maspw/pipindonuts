<?php

namespace App\Filament\Resources\PembelianBahanbakuResource\Pages;

use App\Filament\Resources\PembelianBahanbakuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPembelianBahanbakus extends ListRecords
{
    protected static string $resource = PembelianBahanbakuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
