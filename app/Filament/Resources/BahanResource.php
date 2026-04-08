<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BahanResource\Pages;
use App\Filament\Resources\BahanResource\RelationManagers;
use App\Models\Bahan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BahanResource extends Resource
{
    protected static ?string $model = Bahan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_bahan')->required(),
                Forms\Components\Select::make('satuan')
                    ->options([
                        'kg' => 'Kilogram',
                        'gr' => 'Gram',
                        'liter' => 'Liter',
                        'pcs' => 'Pcs',
                    ])->required(),
                Forms\Components\TextInput::make('stok_qty')->numeric()->default(0),
                
                Forms\Components\FileUpload::make('dokumen')
                ->directory('bahan-dokumen') 
                ->preserveFilenames()
                ->openable()
                ->downloadable(),
                
                Forms\Components\DatePicker::make('tgl_masuk')
                ->label('Tanggal Masuk')
                ->default(now()),
                Forms\Components\DatePicker::make('tgl_kadaluarsa')
                ->label('Tanggal Kadaluarsa'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_bahan')->searchable(),
                Tables\Columns\TextColumn::make('satuan'),
                Tables\Columns\TextColumn::make('stok_qty')->sortable(),
                Tables\Columns\TextColumn::make('tgl_kadaluarsa')->date()->sortable(),
                
                Tables\Columns\TextColumn::make('dokumen')
                ->label('Berkas')
                ->formatStateUsing(fn ($state) => $state ? 'Lihat' : 'Kosong')
                ->url(fn ($record) => $record->dokumen ? asset('storage/' . $record->dokumen) : null, true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('view')
                ->label('Lihat Dokumen')
                ->icon('heroicon-o-eye')
                ->url(fn ($record) => $record->dokumen ? asset('storage/' . $record->dokumen) : null, true)
                ->hidden(fn ($record) => !$record->dokumen),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBahans::route('/'),
            'create' => Pages\CreateBahan::route('/create'),
            'edit' => Pages\EditBahan::route('/{record}/edit'),
        ];
    }
}
