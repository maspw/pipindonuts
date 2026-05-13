<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenjualanProdukResource\Pages;
use App\Models\PenjualanProduk;
use App\Models\Produk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PenjualanProdukResource extends Resource
{
    protected static ?string $model = PenjualanProduk::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // --- FUNGSI HITUNG TOTAL GLOBAL ---
    protected static function updateTotals(Forms\Get $get, Forms\Set $set): void
    {
        $semuaPesanan = $get('harga_jual') ?? [];
        $totalKeseluruhan = 0;

        foreach ($semuaPesanan as $pesanan) {
            $totalKeseluruhan += (int) ($pesanan['subtotal'] ?? 0);
        }

        $set('total_jual', $totalKeseluruhan);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Wizard::make([
                // TAHAP 1: KASIR
                Forms\Components\Wizard\Step::make('Kasir')
                    ->description('Info Kasir & Waktu')
                    ->schema([
                        Forms\Components\Select::make('id_karyawan')
                            ->label('Kasir')
                            ->relationship('karyawan', 'nama')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DateTimePicker::make('tgl_jual')
                            ->label('Tanggal Jual')
                            ->default(now())
                            ->required(),
                    ]),

                // TAHAP 2: PRODUK
                Forms\Components\Wizard\Step::make('Produk')
                    ->description('Pilih Menu Donat')
                    ->schema([
                        Forms\Components\Repeater::make('harga_jual')
                            ->label('Item Pesanan')
                            ->schema([
                                Forms\Components\Select::make('id_produk')
                                    ->label('Nama Produk')
                                    ->relationship('produk', 'nama_produk')
                                    ->required()
                                    ->live(debounce: 300)
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $produk = Produk::find($state);
                                        if ($produk) {
                                            $set('harga_satuan', $produk->harga);
                                            $qty = (int)($get('qty') ?? 1);
                                            $set('subtotal', $qty * $produk->harga);
                                        }
                                        static::updateTotals($get, $set);
                                    })
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                        $harga = (int) $get('harga_satuan');
                                        $set('subtotal', (int)$state * $harga);
                                        static::updateTotals($get, $set);
                                    })
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('harga_satuan')
                                    ->label('Harga')
                                    ->prefix('Rp')
                                    ->readonly()
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Total Item')
                                    ->prefix('Rp')
                                    ->readonly()
                                    ->columnSpan(1),
                            ])
                            ->columns(5)
                            ->addActionLabel('Tambah Rasa Lain')
                            ->live(debounce: 300)
                            // Update total kalau ada baris baru atau hapus baris
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => static::updateTotals($get, $set))
                            ->deleteAction(
                                fn (Forms\Components\Actions\Action $action) => $action->after(fn (Forms\Get $get, Forms\Set $set) => 
                                    static::updateTotals($get, $set)
                                ),
                            ),

                        Forms\Components\Section::make('Ringkasan Pembayaran')->schema([
                            Forms\Components\TextInput::make('total_jual')
                                ->label('Total Yang Harus Dibayar')
                                ->numeric()
                                ->prefix('Rp')
                                ->readonly()
                                ->extraAttributes(['class' => 'text-2xl font-bold text-primary-600']),
                        ]),
                    ]),

                // TAHAP 3: PEMBAYARAN
                Forms\Components\Wizard\Step::make('Pembayaran')
                    ->description('Proses Hitung Kembalian')
                    ->schema([
                        Forms\Components\TextInput::make('uang_diterima')
                            ->label('Uang Diterima')
                            ->prefix('Rp')
                            ->required()
                            ->numeric()
                            ->live(debounce: 300)
                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                $total = (int) $get('total_jual');
                                $uangDiterima = (int) preg_replace('/[^0-9]/', '', $state);

                                if (!$state || $uangDiterima < $total) {
                                    $set('uang_kembalian', 0);
                                    return;
                                }

                                $set('uang_kembalian', $uangDiterima - $total);
                            }),

                        Forms\Components\TextInput::make('uang_kembalian')
                            ->label('Kembalian')
                            ->prefix('Rp')
                            ->readonly()
                            ->placeholder('0'),

                        Forms\Components\Select::make('metode_pembayaran')
                            ->label('Metode Pembayaran')
                            ->options([
                                'Tunai' => 'Tunai',
                                'QRIS' => 'QRIS',
                            ])->default('Tunai')->required(),
                    ]),
            ])->columnSpanFull()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tgl_jual')
                    ->label('Tanggal & Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Kasir')
                    ->searchable(),

                Tables\Columns\TextColumn::make('harga_jual')
                    ->label('Detail Donat')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $items = $record->harga_jual ?? [];
                        $tampilan = "";
                        foreach ($items as $item) {
                            $produk = Produk::find($item['id_produk']);
                            $namaDonat = $produk ? $produk->nama_produk : 'Produk';
                            $qty = $item['qty'] ?? 0;
                            $tampilan .= "• {$namaDonat} ({$qty} pcs)<br>";
                        }
                        return $tampilan ?: '-';
                    }),

                Tables\Columns\TextColumn::make('total_jual')
                    ->label('Total Bayar')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->label('Metode'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('cetak_struk')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn(PenjualanProduk $record): string => route('penjualan_produk.print', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPenjualanProduks::route('/'),
            'create' => Pages\CreatePenjualanProduk::route('/create'),
            'edit' => Pages\EditPenjualanProduk::route('/{record}/edit'),
        ];
    }
}