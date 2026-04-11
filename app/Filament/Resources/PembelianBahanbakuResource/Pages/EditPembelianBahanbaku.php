<?php

namespace App\Filament\Resources\PembelianBahanbakuResource\Pages;

use App\Filament\Resources\PembelianBahanbakuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPembelianBahanbaku extends EditRecord
{
    protected static string $resource = PembelianBahanbakuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Hapus'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
