<?php

namespace App\Filament\Resources;

use App\Models\Karyawan;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\Facades\DB;
use App\Filament\Resources\KaryawanResource\Pages;

class KaryawanResource extends Resource
{
    protected static ?string $model = Karyawan::class;

    protected static ?string $navigationLabel = 'Karyawan';
    //protected static ?string $navigationGroup = 'Master Data';//
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form->schema([

            TextInput::make('id_karyawan')
                ->label('ID Karyawan')
                ->disabled()
                ->dehydrated(),

            TextInput::make('nama')
                ->required(),

            TextInput::make('no_telp')
                ->label('No Telepon')
                ->required()
                ->tel(),

            Select::make('posisi')
                ->required()
                ->options([
                    'kasir' => 'Kasir',
                    'bag_gudang' => 'Bag. Gudang',
                ])
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {

                    if ($state == 'kasir') {
                        $prefix = 'KSR';
                    } else {
                        $prefix = 'GD';
                    }

                    $last = DB::table('karyawans')
                        ->where('posisi', $state)
                        ->count();

                    $kode = $prefix . str_pad($last + 1, 3, '0', STR_PAD_LEFT);

                    $set('id_karyawan', $kode);
                }),

            DatePicker::make('tanggal_masuk')
                ->required(),

            FileUpload::make('e_ktp')
                ->label('Upload E-KTP')
                ->image()
                ->directory('e-ktp')
                ->acceptedFileTypes(['image/jpeg', 'image/png'])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_karyawan')->label('ID'),
                TextColumn::make('nama'),
                TextColumn::make('no_telp')->label('No HP'),
                TextColumn::make('posisi'),
                TextColumn::make('tanggal_masuk')->date(),
                ImageColumn::make('e_ktp')->label('E-KTP'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKaryawans::route('/'),
            'create' => Pages\CreateKaryawan::route('/create'),
            'edit' => Pages\EditKaryawan::route('/{record}/edit'),
        ];
    }
}
