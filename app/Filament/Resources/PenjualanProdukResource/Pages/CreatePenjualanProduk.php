<?php

namespace App\Filament\Resources\PenjualanProdukResource\Pages;

use App\Filament\Resources\PenjualanProdukResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePenjualanProduk extends CreateRecord
{
    protected static string $resource = PenjualanProdukResource::class;

    // Supaya setelah simpan langsung balik ke tabel utama
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}