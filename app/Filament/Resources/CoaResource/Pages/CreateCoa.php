<?php

namespace App\Filament\Resources\CoaResource\Pages;

use App\Filament\Resources\CoaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCoa extends CreateRecord //membuat halaman tambah data coa
{
    protected static string $resource = CoaResource::class; //halaman ini terhubung ke coa resource
}
