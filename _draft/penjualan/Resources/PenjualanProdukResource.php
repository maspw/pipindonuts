<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenjualanProdukResource\Pages;
use App\Models\Karyawan;
use App\Models\PenjualanProduk;
use App\Models\Produk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PenjualanProdukResource extends Resource
{
    protected static ?string $model          = PenjualanProduk::class;
    protected static ?string $navigationIcon  = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Penjualan Produk';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $modelLabel      = 'Penjualan';
    protected static ?string $pluralModelLabel = 'Penjualan Produk';
    protected static ?int    $navigationSort  = 1;

    // ─────────────────────────────────────────────────────────
    // FORM
    // Sesuai ERD:
    //   penjualan_produks : id_penjualan, karyawan_id, tgl_jual, total_jual
    //   detil_penjualans  : id_penjualan, produk_id, jumlah, harga_satuan, sub_total
    //   pembayarans       : id_pembayaran, id_penjualan, metode_bayar,
    //                       total_bayar, kembalian, status_bayar
    // ─────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── Header Transaksi ──────────────────────────────
            Forms\Components\Section::make('Informasi Transaksi')
                ->description('Data utama transaksi penjualan')
                ->icon('heroicon-o-document-text')
                ->schema([

                    // ERD: id_penjualan (PK string, auto-generate)
                    Forms\Components\TextInput::make('id_penjualan')
                        ->label('ID Penjualan')
                        ->default(fn () => PenjualanProduk::generateIdPublic())
                        ->disabled()
                        ->dehydrated()
                        ->required(),

                    // ERD: karyawan_id (FK → karyawans.id_karyawan, nullable)
                    Forms\Components\Select::make('karyawan_id')
                        ->label('Karyawan')
                        ->options(
                            Karyawan::all()->mapWithKeys(fn ($k) => [
                                $k->id_karyawan => $k->nama . ' — ' . $k->posisi,
                            ])
                        )
                        ->searchable()
                        ->native(false)
                        ->nullable()
                        ->placeholder('Pilih karyawan (opsional)'),

                    // ERD: tgl_jual (date)
                    Forms\Components\DatePicker::make('tgl_jual')
                        ->label('Tanggal Jual')
                        ->default(now())
                        ->required()
                        ->native(false)
                        ->displayFormat('d M Y'),

                ])
                ->columns(3),

            // ── Detail Produk (Repeater → detil_penjualans) ───
            Forms\Components\Section::make('Detail Produk')
                ->description('Produk yang dijual dalam transaksi ini')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([

                    Forms\Components\Repeater::make('detil')
                        ->label('')
                        ->relationship('detil')
                        ->schema([

                            // ERD: produk_id (FK → produk.id_produk)
                            Forms\Components\Select::make('produk_id')
                                ->label('Produk')
                                ->options(
                                    Produk::all()->mapWithKeys(fn ($p) => [
                                        $p->id_produk => $p->nama_produk,
                                    ])
                                )
                                ->required()
                                ->searchable()
                                ->native(false)
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set) {
                                    $produk = Produk::find($state);
                                    if ($produk) {
                                        // ERD: harga_satuan diisi dari harga produk
                                        $set('harga_satuan', $produk->harga);
                                        $set('sub_total', $produk->harga); // qty default 1
                                    }
                                })
                                ->columnSpan(4),

                            // ERD: harga_satuan (bigInteger)
                            Forms\Components\TextInput::make('harga_satuan')
                                ->label('Harga Satuan')
                                ->numeric()
                                ->prefix('Rp')
                                ->readOnly()
                                ->dehydrated()
                                ->columnSpan(2),

                            // ERD: jumlah (integer)
                            Forms\Components\TextInput::make('jumlah')
                                ->label('Jumlah')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required()
                                ->live(debounce: 400)
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    // ERD: sub_total = harga_satuan × jumlah
                                    $harga = (int) $get('harga_satuan');
                                    $set('sub_total', $harga * max(1, (int) $state));
                                })
                                ->columnSpan(2),

                            // ERD: sub_total (bigInteger)
                            Forms\Components\TextInput::make('sub_total')
                                ->label('Sub Total')
                                ->numeric()
                                ->prefix('Rp')
                                ->readOnly()
                                ->dehydrated()
                                ->columnSpan(2),

                        ])
                        ->columns(10)
                        ->addActionLabel('+ Tambah Produk')
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::hitungTotal($get, $set))
                        ->deleteAction(
                            fn ($action) => $action->after(
                                fn (Get $get, Set $set) => self::hitungTotal($get, $set)
                            )
                        ),

                ]),

            // ── Total + Pembayaran ────────────────────────────
            Forms\Components\Section::make('Pembayaran')
                ->description('Total transaksi dan data pembayaran')
                ->icon('heroicon-o-banknotes')
                ->schema([

                    // ERD: total_jual (bigInteger) — dihitung otomatis
                    Forms\Components\TextInput::make('total_jual')
                        ->label('Total Jual')
                        ->numeric()
                        ->prefix('Rp')
                        ->readOnly()
                        ->dehydrated(),

                    // ── HasOne: pembayarans ───────────────────
                    Forms\Components\Fieldset::make('Data Pembayaran')
                        ->relationship('pembayaran')
                        ->schema([

                            // ERD: metode_bayar (string: tunai | transfer | qris)
                            Forms\Components\Select::make('metode_bayar')
                                ->label('Metode Bayar')
                                ->options([
                                    'tunai'    => '💵 Tunai',
                                    'transfer' => '🏦 Transfer',
                                    'qris'     => '📱 QRIS',
                                ])
                                ->default('tunai')
                                ->required()
                                ->native(false),

                            // ERD: total_bayar (bigInteger)
                            Forms\Components\TextInput::make('total_bayar')
                                ->label('Total Bayar')
                                ->numeric()
                                ->prefix('Rp')
                                ->required()
                                ->live(debounce: 400)
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    // ERD: kembalian = total_bayar − total_jual
                                    $totalJual = (int) $get('../../total_jual');
                                    $set('kembalian', max(0, (int) $state - $totalJual));
                                }),

                            // ERD: kembalian (bigInteger)
                            Forms\Components\TextInput::make('kembalian')
                                ->label('Kembalian')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->readOnly()
                                ->dehydrated(),

                            // ERD: status_bayar (string: lunas | hutang)
                            Forms\Components\Select::make('status_bayar')
                                ->label('Status Bayar')
                                ->options([
                                    'lunas'  => '✅ Lunas',
                                    'hutang' => '⚠️ Hutang',
                                ])
                                ->default('lunas')
                                ->required()
                                ->native(false),

                        ])
                        ->columns(2),

                ])
                ->columns(1),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // TABLE
    // Kolom sesuai atribut ERD masing-masing tabel
    // ─────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // penjualan_produks.id_penjualan
                Tables\Columns\TextColumn::make('id_penjualan')
                    ->label('ID Penjualan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // penjualan_produks.karyawan_id → karyawans.nama
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable()
                    ->default('-'),

                // penjualan_produks.tgl_jual
                Tables\Columns\TextColumn::make('tgl_jual')
                    ->label('Tgl Jual')
                    ->date('d M Y')
                    ->sortable(),

                // penjualan_produks.total_jual
                Tables\Columns\TextColumn::make('total_jual')
                    ->label('Total Jual')
                    ->money('IDR')
                    ->sortable(),

                // pembayarans.metode_bayar
                Tables\Columns\TextColumn::make('pembayaran.metode_bayar')
                    ->label('Metode Bayar')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'tunai'    => 'success',
                        'transfer' => 'info',
                        'qris'     => 'warning',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'tunai'    => 'Tunai',
                        'transfer' => 'Transfer',
                        'qris'     => 'QRIS',
                        default    => '-',
                    }),

                // pembayarans.total_bayar
                Tables\Columns\TextColumn::make('pembayaran.total_bayar')
                    ->label('Total Bayar')
                    ->money('IDR')
                    ->toggleable(isToggledHiddenByDefault: true),

                // pembayarans.kembalian
                Tables\Columns\TextColumn::make('pembayaran.kembalian')
                    ->label('Kembalian')
                    ->money('IDR')
                    ->toggleable(isToggledHiddenByDefault: true),

                // pembayarans.status_bayar
                Tables\Columns\TextColumn::make('pembayaran.status_bayar')
                    ->label('Status Bayar')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'lunas'  => 'success',
                        'hutang' => 'danger',
                        default  => 'gray',
                    }),
            ])
            ->defaultSort('tgl_jual', 'desc')
            ->filters([
                // Filter by pembayarans.metode_bayar
                Tables\Filters\SelectFilter::make('metode_bayar')
                    ->label('Metode Bayar')
                    ->relationship('pembayaran', 'metode_bayar')
                    ->options([
                        'tunai'    => 'Tunai',
                        'transfer' => 'Transfer',
                        'qris'     => 'QRIS',
                    ])
                    ->native(false),

                // Filter by pembayarans.status_bayar
                Tables\Filters\SelectFilter::make('status_bayar')
                    ->label('Status Bayar')
                    ->relationship('pembayaran', 'status_bayar')
                    ->options([
                        'lunas'  => 'Lunas',
                        'hutang' => 'Hutang',
                    ])
                    ->native(false),

                // Filter by penjualan_produks.karyawan_id
                Tables\Filters\SelectFilter::make('karyawan_id')
                    ->label('Karyawan')
                    ->relationship('karyawan', 'nama')
                    ->searchable()
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Detail'),
                Tables\Actions\EditAction::make()->label('Edit'),
                Tables\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->emptyStateHeading('Belum ada transaksi penjualan')
            ->emptyStateDescription('Buat transaksi baru dengan klik tombol di atas.');
    }

    // ─────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────

    /**
     * Hitung ulang total_jual = Σ sub_total dari repeater detil.
     */
    private static function hitungTotal(Get $get, Set $set): void
    {
        $items = $get('detil') ?? [];
        $total = collect($items)->sum(fn ($item) => (int) ($item['sub_total'] ?? 0));
        $set('total_jual', $total);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPenjualanProduks::route('/'),
            'create' => Pages\CreatePenjualanProduk::route('/create'),
            'edit'   => Pages\EditPenjualanProduk::route('/{record}/edit'),
        ];
    }
}
