<?php

namespace App\Filament\Resources\ReturPembelianResource\Pages;

use App\Filament\Resources\ReturPembelianResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReturPembelian extends CreateRecord
{
    protected static string $resource = ReturPembelianResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
