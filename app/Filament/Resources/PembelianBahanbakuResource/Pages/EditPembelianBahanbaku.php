<?php

namespace App\Filament\Resources\PembelianBahanbakuResource\Pages;

use App\Filament\Resources\PembelianBahanbakuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB; 

class EditPembelianBahanbaku extends EditRecord
{
    protected static string $resource = PembelianBahanbakuResource::class;

    protected function getSavedNotificationTitle(): ?string 
    {
        return 'Data Pembayaran Berhasil Diperbarui';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus')
                ->before(function () {
                    $pembelian = $this->record;

                    foreach ($pembelian->detail_pembelian as $detail) {
                        DB::table('bahans')
                            ->where('id_bahan', $detail->id_bahanbaku)
                            ->decrement('jml_stok', $detail->jumlah); 
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string 
    {
        return $this->getResource()::getUrl('index');
    }

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
}