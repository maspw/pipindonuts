<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use App\Models\Bahan;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class StokBahanBakuMenipisWidget extends BaseWidget
{
    protected static ?string $heading = '⚠️ Stok Bahan Baku Menipis';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Bahan::query()
                    ->whereColumn('jml_stok', '<=', 'stok_minimum')
                    ->orderBy('jml_stok', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama_bahan')
                    ->label('Nama Bahan'),

                Tables\Columns\TextColumn::make('jml_stok')
                    ->label('Stok Saat Ini')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state <= 3 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('stok_minimum')
                    ->label('Stok Minimum'),

                Tables\Columns\TextColumn::make('satuan')
                    ->label('Satuan'),
            ]);
    }
}
