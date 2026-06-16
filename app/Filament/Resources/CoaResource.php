<?php

namespace App\Filament\Resources;

// Tambahan
use Filament\Forms\Components\TextInput; //kita menggunakan textinput
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;

use App\Filament\Resources\CoaResource\Pages;
use App\Filament\Resources\CoaResource\RelationManagers;
use App\Models\Coa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CoaResource extends Resource //file ini penngatur crud tabel coa di filament
{
    protected static ?string $model = Coa::class; //model terhubung ke coa

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Chart of Accounts';

    protected static ?string $modelLabel = 'Akun';

    protected static ?string $pluralModelLabel = 'Chart of Accounts';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form //mengatur tampilan form
    {
        return $form
            ->schema([
                //isikan dengan input type form
                Grid::make(1)
    ->schema([

        TextInput::make('kode_akun')
            ->required()
            ->placeholder('Masukkan kode akun'),

        TextInput::make('nama_akun')
            ->autocapitalize('words')
            ->label('Nama akun')
            ->required()
            ->placeholder('Masukkan nama akun'),

        Forms\Components\Select::make('header_akun')
            ->options([
                'Aset' => 'Aset',
                'Utang' => 'Utang',
                'Modal' => 'Modal',
                'Pendapatan' => 'Pendapatan',
                'Beban' => 'Beban',
            ])
            ->required(),

    ]),
            ]);
    }

    public static function table(Table $table): Table //mengatur tampilan tabel di admin
    {
        return $table
            ->columns([

    TextColumn::make('kode_akun'),

    TextColumn::make('nama_akun'),

    TextColumn::make('header_akun'),

])
        
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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

    public static function getPages(): array //mengatur halaman utama
    {
        return [
            'index' => Pages\ListCoas::route('/'),
            'create' => Pages\CreateCoa::route('/create'),
            'edit' => Pages\EditCoa::route('/{record}/edit'),
        ];
    }
}