<?php

namespace App\Filament\Resources\ReturPembelianResource\Pages;

use App\Filament\Resources\ReturPembelianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReturPembelian extends EditRecord
{
    protected static string $resource = ReturPembelianResource::class;

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
