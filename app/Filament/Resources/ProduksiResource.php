<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProduksiResource\Pages;
use App\Models\Produksi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Import Komponen Form
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;

// Import Komponen Table & Action
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;

class ProduksiResource extends Resource
{
    protected static ?string $model = Produksi::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 3; 

    protected static ?string $navigationLabel = 'Produksi';
    protected static ?string $pluralModelLabel = 'Produksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // STEP 1: DATA UTAMA
                    Wizard\Step::make('Data Produksi')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            TextInput::make('id_produksi')
                                ->label('ID Produksi')
                                ->default(fn () => Produksi::generateId())
                                ->readonly()
                                ->required(),

                            Select::make('id_karyawan')
                                ->label('Karyawan')
                                ->relationship('karyawan', 'nama')
                                ->searchable()
                                ->preload()
                                ->required(),

                            DatePicker::make('tgl_produksi')
                                ->label('Tanggal Produksi')
                                ->default(now())
                                ->required(),

                            Textarea::make('catatan')
                                ->label('Catatan Tambahan')
                                ->placeholder('Opsional...')
                                ->columnSpanFull(),
                        ]),

                    // STEP 2: DETAIL BAHAN BAKU
                    Wizard\Step::make('Detail Bahan Baku')
                        ->icon('heroicon-o-beaker')
                        ->description('Pilih bahan yang digunakan dalam produksi ini')
                        ->schema([
                            Repeater::make('detailBahanProduksi')
                                ->relationship() 
                                ->schema([
                                    Select::make('id_bahanbaku')
                                        ->label('Bahan Baku')
                                        ->relationship('bahanBaku', 'nama_bahan')
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    
                                    TextInput::make('jumlah_dipakai')
                                        ->label('Jumlah Pakai')
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(1)
                                        ->required(),
                                ])
                                ->columns(2)
                                ->addActionLabel('Tambah Bahan Lainnya')
                                ->columnSpanFull(),
                        ]),

                    // STEP 3: STATUS (Paling Akhir)
                    Wizard\Step::make('Status Produksi')
                        ->icon('heroicon-o-check-circle')
                        ->schema([
                            Select::make('status')
                                ->label('Status Akhir')
                                ->options([
                                    'proses' => 'Proses',
                                    'selesai' => 'Selesai',
                                ])
                                ->default('proses')
                                ->required()
                                ->native(false),
                        ])
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_produksi')
                    ->label('ID Produksi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('karyawan.nama')
                    ->label('Karyawan')
                    ->searchable(),

                TextColumn::make('tgl_produksi')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'proses' => 'warning',
                        'selesai' => 'success',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('id_produksi', 'desc')
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProduksis::route('/'),
            'create' => Pages\CreateProduksi::route('/create'),
            'edit' => Pages\EditProduksi::route('/{record}/edit'),
            'view' => Pages\ViewProduksi::route('/{record}'),
        ];
    }
}