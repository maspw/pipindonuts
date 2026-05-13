<?php

namespace App\Filament\Resources\CoaResource\Pages;

use App\Filament\Resources\CoaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoa extends EditRecord //halaman edit coa
{
    protected static string $resource = CoaResource::class; //mengambil form dari coa resource

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(), //menambhakan tombol delete di halaman edit
        ];
    }
}
