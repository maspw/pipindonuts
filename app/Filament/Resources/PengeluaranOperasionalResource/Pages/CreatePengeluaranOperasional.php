<?php

namespace App\Filament\Resources\PengeluaranOperasionalResource\Pages;

use App\Filament\Resources\PengeluaranOperasionalResource;

use Filament\Resources\Pages\CreateRecord;

use Filament\Actions\Action;

class CreatePengeluaranOperasional extends CreateRecord
{
    protected static string $resource =
        PengeluaranOperasionalResource::class;

    // TOMBOL BAYAR
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Create')
            ->color('success')
            ->icon('heroicon-o-credit-card');
    }

    // HAPUS CREATE ANOTHER
    public static function canCreateAnother(): bool
    {
        return false;
    }

    // TOMBOL CANCEL
    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Cancel')
            ->color('gray');
    }
}