<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Supplier';
    protected static ?string $modelLabel = 'Supplier';
    protected static ?string $pluralModelLabel = 'Data Supplier';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Input Data Supplier')
                    ->description('Lengkapi form di bawah ini untuk mengelola data master supplier.')
                    ->schema([
                        TextInput::make('id_supplier')
                            ->label('ID Supplier')
                            ->default(function () {
                                $latest = \App\Models\Supplier::orderBy('id_supplier', 'desc')->first();
                                if (!$latest) return 'SUP001';
                                
                                $number = (int) filter_var($latest->id_supplier, FILTER_SANITIZE_NUMBER_INT) + 1;
                                return 'SUP' . str_pad($number, 3, '0', STR_PAD_LEFT);
                            })
                            ->readOnly(),

                        TextInput::make('nama_supplier')
                            ->label('Nama Supplier')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: PT. Maju Jaya'),

                        TextInput::make('no_telp')
                            ->label('No. Telepon')
                            ->tel()
                            ->required()
                            ->maxLength(20)
                            ->placeholder('Contoh: 08123456789'), // SUDAH DIISI BIAR NGGAK ERROR

                        Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_supplier')
                    ->label('ID Supplier')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('nama_supplier')
                    ->label('Nama Supplier')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('no_telp')
                    ->label('No. Telepon')
                    ->icon('heroicon-o-phone')
                    ->copyable()
                    ->color('gray'),

                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->alamat),

                TextColumn::make('created_at')
                    ->label('Tgl Input')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id_supplier', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()->label('Edit'),
                Tables\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus yang Dipilih'),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-building-storefront')
            ->emptyStateHeading('Belum ada supplier')
            ->emptyStateDescription('Tambahkan supplier pertama untuk mulai mencatat pembelian bahan baku.');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit'   => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}