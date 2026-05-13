<?php

namespace App\Filament\Resources\PenjualanProdukResource\Pages;

use App\Filament\Resources\PenjualanProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPenjualanProduk extends EditRecord
{
    protected static string $resource = PenjualanProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Hapus Transaksi'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Hitung ulang total_jual dari detil
        $total = collect($data['detil'] ?? [])->sum(fn ($d) => (int) ($d['sub_total'] ?? 0));
        if ($total > 0) {
            $data['total_jual'] = $total;
        }

        return $data;
    }
}
