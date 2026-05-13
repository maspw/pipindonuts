<?php

namespace App\Filament\Resources\PembelianBahanbakuResource\Pages;

use App\Filament\Resources\PembelianBahanbakuResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePembelianBahanbaku extends CreateRecord
{
    protected static string $resource = PembelianBahanbakuResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pastikan total_beli terisi
        $data['total_beli'] = $data['total_beli'] ?? 0;
        return $data;
    }
}
