<?php

namespace App\Filament\Resources\PembelianBahanbakuResource\Pages;

use App\Filament\Resources\PembelianBahanbakuResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoicePembelianMail;

class CreatePembelianBahanbaku extends CreateRecord
{
    protected static string $resource = PembelianBahanbakuResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pembayaran Berhasil';
    }
protected function afterCreate(): void
{
    $pembelian = $this->record;

    foreach ($pembelian->detail_pembelian as $detail) {
        DB::table('bahans') 
            ->where('id_bahanbaku', $detail->id_bahanbaku) 
            ->increment('jml_stok', $detail->jumlah); 
    }

    try {
        Mail::to('admin@mailtrap.io')->send(new InvoicePembelianMail($pembelian));
    } catch (\Exception $e) {
        // 
    }
}

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['total_beli'] = $data['total_beli'] ?? 0;
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}