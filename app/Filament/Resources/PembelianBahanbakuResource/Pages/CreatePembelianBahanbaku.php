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

    public function konfirmasiBayarAction(): Action
    {
        return Action::make('konfirmasiBayar')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Pembayaran')
            ->modalSubmitActionLabel('Ya, Bayar')
            ->action(fn () => $this->create());
    }

    protected function afterCreate(): void
    {
        $pembelian = $this->record;
        foreach ($pembelian->detail_pembelian as $detail) {
            DB::table('bahans')
                ->where('id', $detail->id_bahanbaku)
                ->increment('stok_qty', $detail->jumlah);
        }
        try {
            Mail::to('admin@mailtrap.io')->send(new InvoicePembelianMail($pembelian));
        } catch (\Exception $e) { }
    }

    protected function getRedirectUrl(): string 
    { 
        return $this->getResource()::getUrl('index'); 
    }
}