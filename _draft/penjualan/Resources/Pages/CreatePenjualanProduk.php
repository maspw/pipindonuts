<?php

namespace App\Filament\Resources\PenjualanProdukResource\Pages;

use App\Filament\Resources\PenjualanProdukResource;
use App\Models\PenjualanProduk;
use Filament\Resources\Pages\CreateRecord;

class CreatePenjualanProduk extends CreateRecord
{
    protected static string $resource = PenjualanProdukResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pastikan total_jual dihitung dari detil jika tidak terisi
        if (empty($data['total_jual'])) {
            $total = collect($data['detil'] ?? [])->sum(fn ($d) => (int) ($d['sub_total'] ?? 0));
            $data['total_jual'] = $total;
        }

        return $data;
    }
}
