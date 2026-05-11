<?php


namespace App\Filament\Resources;

use App\Filament\Resources\PembayaranResource\Pages;
use App\Models\Pembayaran;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class PembayaranResource extends Resource
{
    protected static ?string $model = Pembayaran::class;

    // Ikon untuk sidebar
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    // Label di sidebar
    protected static ?string $navigationLabel = 'Riwayat Pembayaran';

    protected static ?string $navigationGroup = 'Transaksi';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_penjualan')
                    ->label('ID Transaksi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('penjualan.karyawan_id')
                    ->label('Kasir'),

                TextColumn::make('metode_bayar')
                    ->label('Metode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tunai' => 'success',
                        'transfer' => 'info',
                        'qris' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('total_bayar')
                    ->label('Jumlah Bayar')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('kembalian')
                    ->label('Kembalian')
                    ->money('IDR'),

                TextColumn::make('status_bayar')
                    ->label('Status')
                    ->badge()
                    ->color('success'),

                TextColumn::make('created_at')
                    ->label('Waktu Transaksi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                // Kamu bisa tambah filter berdasarkan metode bayar di sini
            ])
            ->actions([
                // Tombol view jika ingin melihat detail lebih dalam
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembayarans::route('/'),
        ];
    }
}