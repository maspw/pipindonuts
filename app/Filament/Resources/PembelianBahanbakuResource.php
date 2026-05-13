<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembelianBahanbakuResource\Pages;
use App\Models\Bahan;
use App\Models\Karyawan;
use App\Models\PembelianBahanbaku;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PembelianBahanbakuResource extends Resource
{
    protected static ?string $model = PembelianBahanbaku::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pembelian Bahan Baku';

    protected static ?string $modelLabel = 'Pembelian Bahan Baku';

    protected static ?string $pluralModelLabel = 'Pembelian Bahan Baku';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Pembelian')
                    ->description('Informasi transaksi pembelian dari supplier')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier')
                            ->options(Supplier::all()->pluck('nama_supplier', 'id'))
                            ->required()
                            ->searchable()
                            ->native(false),

                        Forms\Components\Select::make('karyawan_id')
                            ->label('Karyawan (Penerima)')
                            ->options(
                                Karyawan::all()->mapWithKeys(fn ($k) => [
                                    $k->id_karyawan => $k->nama . ' (' . $k->posisi . ')',
                                ])
                            )
                            ->required()
                            ->searchable()
                            ->native(false),

                        Forms\Components\DatePicker::make('tgl_beli')
                            ->label('Tanggal Beli')
                            ->required()
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->default(now()),

                        Forms\Components\FileUpload::make('dokumen')
                            ->label('Invoice / Nota Pembelian')
                            ->directory('invoice-pembelian')
                            ->preserveFilenames()
                            ->openable()
                            ->downloadable()
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->helperText('Upload invoice/nota dari supplier (PDF atau gambar)'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Item Pembelian')
                    ->description('Daftar bahan baku yang dibeli')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        Forms\Components\Repeater::make('detilPembelian')
                            ->relationship('detilPembelian')
                            ->label('')
                            ->schema([
                                Forms\Components\Select::make('bahan_id')
                                    ->label('Bahan')
                                    ->options(Bahan::all()->pluck('nama_bahan', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $bahan = Bahan::find($get('bahan_id'));
                                        if ($bahan) {
                                            $set('jumlah', $bahan->jml_stok);
                                            $harga = (int) $get('harga_satuan') ?: 0;
                                            $set('sub_total', $bahan->jml_stok * $harga);
                                        } else {
                                            $set('jumlah', null);
                                            $set('sub_total', 0);
                                        }
                                    })
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('jumlah')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->suffix(fn (Get $get) => Bahan::find($get('bahan_id'))?->satuan ?? '')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $jumlah = (int) $get('jumlah') ?: 0;
                                        $harga  = (int) $get('harga_satuan') ?: 0;
                                        $set('sub_total', $jumlah * $harga);
                                    }),

                                Forms\Components\TextInput::make('harga_satuan')
                                    ->label('Harga Satuan (Rp)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->prefix('Rp')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $jumlah = (int) $get('jumlah') ?: 0;
                                        $harga  = (int) $get('harga_satuan') ?: 0;
                                        $set('sub_total', $jumlah * $harga);
                                    }),

                                Forms\Components\TextInput::make('sub_total')
                                    ->label('Sub Total (Rp)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0),

                                Forms\Components\DatePicker::make('tgl_kadaluarsa')
                                    ->label('Tgl Kadaluarsa Batch Ini')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->nullable(),
                            ])
                            ->columns(3)
                            ->addActionLabel('+ Tambah Bahan')
                            ->reorderable()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::hitungTotal($get, $set);
                            })
                            ->deleteAction(
                                fn ($action) => $action->after(function (Get $get, Set $set) {
                                    self::hitungTotal($get, $set);
                                })
                            ),
                    ]),

                Forms\Components\Section::make('')
                    ->schema([
                        Forms\Components\TextInput::make('total_beli')
                            ->label('Total Pembelian')
                            ->prefix('Rp')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                    ]),
            ]);
    }

    protected static function hitungTotal(Get $get, Set $set): void
    {
        $items = $get('detilPembelian') ?? [];
        $total = collect($items)->sum(fn ($item) => (int) ($item['sub_total'] ?? 0));
        $set('total_beli', $total);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Karyawan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tgl_beli')
                    ->label('Tanggal Beli')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_beli')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('detil_pembelian_count')
                    ->label('Jenis Bahan')
                    ->counts('detilPembelian')
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('dokumen')
                    ->label('Invoice')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->defaultSort('tgl_beli', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'nama_supplier')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\Action::make('lihat_invoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-paper-clip')
                    ->color('info')
                    ->url(fn ($record) => $record->dokumen ? asset('storage/' . $record->dokumen) : null, shouldOpenInNewTab: true)
                    ->hidden(fn ($record) => !$record->dokumen),
                Tables\Actions\EditAction::make()->label('Edit'),
                Tables\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus yang Dipilih'),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->emptyStateHeading('Belum ada transaksi pembelian')
            ->emptyStateDescription('Catat pembelian bahan baku pertama dengan klik tombol di atas.');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPembelianBahanbakus::route('/'),
            'create' => Pages\CreatePembelianBahanbaku::route('/create'),
            'edit'   => Pages\EditPembelianBahanbaku::route('/{record}/edit'),
        ];
    }
}
