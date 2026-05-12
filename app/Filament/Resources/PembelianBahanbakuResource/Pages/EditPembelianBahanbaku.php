<?php

namespace App\Filament\Resources\PembelianBahanbakuResource\Pages;

use App\Filament\Resources\PembelianBahanbakuResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditPembelianBahanbaku extends EditRecord
{
    protected static string $resource = PembelianBahanbakuResource::class;

    protected function getSavedNotificationTitle(): ?string {
        return 'Data Pembayaran Berhasil Diperbarui';
    }

    // FUNGSI INI AKAN MEMUNCULKAN POP-UP KONFIRMASI DARI WIZARD
    public function konfirmasiBayarAction(): Action
    {
        return Action::make('konfirmasiBayar')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Pembayaran')
            ->modalDescription('Apakah Anda yakin ingin memproses data ini?')
            ->modalSubmitActionLabel('Ya, Bayar')
            ->action(function () {
                $this->save();
            });
    }

    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index');
    }
}