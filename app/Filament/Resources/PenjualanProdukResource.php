<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenjualanProdukResource\Pages;

use App\Models\Karyawan;
use App\Models\PenjualanProduk;
use App\Models\Produk;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

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
    // FORM (dipakai halaman Edit — Create pakai Wizard di Pages)
    // ─────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Informasi Transaksi')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Forms\Components\TextInput::make('id_penjualan')
                        ->label('ID Penjualan')
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\Select::make('karyawan_id')
                        ->label('Karyawan')
                        ->options(
                            Karyawan::all()->mapWithKeys(fn ($k) => [
                                $k->id_karyawan => $k->nama . ' — ' . $k->posisi,
                            ])
                        )
                        ->searchable()
                        ->native(false)
                        ->nullable(),

                    Forms\Components\DatePicker::make('tgl_jual')
                        ->label('Tanggal Jual')
                        ->required()
                        ->native(false)
                        ->displayFormat('d M Y'),
                ])
                ->columns(3),

            Forms\Components\Section::make('Detail Produk')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([
                    Forms\Components\Repeater::make('detil')
                        ->label('')
                        ->relationship('detil')
                        ->schema([
                            Forms\Components\Select::make('produk_id')
                                ->label('Produk')
                                ->options(
                                    Produk::all()->mapWithKeys(fn ($p) => [
                                        $p->id_produk => $p->nama_produk
                                            . ' — Rp ' . number_format($p->harga, 0, ',', '.')
                                            . ' | Stok: ' . $p->stok,
                                    ])
                                )
                                ->required()
                                ->searchable()
                                ->native(false)
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set) {
                                    $produk = Produk::find($state);
                                    if ($produk) {
                                        $set('harga_satuan', $produk->harga);
                                        $set('sub_total', $produk->harga);
                                    }
                                })
                                ->columnSpan(4),

                            Forms\Components\TextInput::make('harga_satuan')
                                ->label('Harga Satuan')
                                ->numeric()
                                ->prefix('Rp')
                                ->readOnly()
                                ->dehydrated()
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('jumlah')
                                ->label('Jumlah')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required()
                                ->live(debounce: 400)
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    $set('sub_total', (int) $get('harga_satuan') * max(1, (int) $state));
                                })
                                ->columnSpan(2),

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

            Forms\Components\Section::make('Pembayaran')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    Forms\Components\TextInput::make('total_jual')
                        ->label('Total Jual')
                        ->numeric()
                        ->prefix('Rp')
                        ->readOnly()
                        ->dehydrated(),

                    Forms\Components\Fieldset::make('Data Pembayaran')
                        ->relationship('pembayaran')
                        ->schema([
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

                            Forms\Components\TextInput::make('total_bayar')
                                ->label('Total Bayar')
                                ->numeric()
                                ->prefix('Rp')
                                ->required()
                                ->live(debounce: 400)
                                ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                    // path: pembayaran.total_bayar → naik 1 level → total_jual
                                    $totalJual = (int) $get('../total_jual');
                                    $set('kembalian', max(0, (int) $state - $totalJual));
                                }),

                            Forms\Components\TextInput::make('kembalian')
                                ->label('Kembalian')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->readOnly()
                                ->dehydrated(),

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
    // ─────────────────────────────────────────────────────────
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
                Tables\Columns\TextColumn::make('id_penjualan')
                    ->label('ID Penjualan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('tgl_jual')
                    ->label('Tgl Jual')
                    ->date('d M Y')
                    ->sortable(),

                // Total item terjual (sum detil.jumlah) — tampil sebagai "X pcs"
                Tables\Columns\TextColumn::make('total_item')
                    ->label('Total Item')
                    ->getStateUsing(fn ($record) => $record->detil->sum('jumlah'))
                    ->suffix(' pcs')
                    ->badge()
                    ->color('info'),



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

                Tables\Columns\TextColumn::make('pembayaran.total_bayar')
                    ->label('Total Bayar')
                    ->money('IDR')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('pembayaran.kembalian')
                    ->label('Kembalian')
                    ->money('IDR')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_jual')
                    ->label('Total Pembelian')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultSort('tgl_jual', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('metode_bayar')
                    ->label('Metode Bayar')
                    ->relationship('pembayaran', 'metode_bayar')
                    ->options([
                        'tunai'    => 'Tunai',
                        'transfer' => 'Transfer',
                        'qris'     => 'QRIS',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('status_bayar')
                    ->label('Status Bayar')
                    ->relationship('pembayaran', 'status_bayar')
                    ->options([
                        'lunas'  => 'Lunas',
                        'hutang' => 'Hutang',
                    ])
                    ->native(false),

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

                    // ── Bulk Export PDF ───────────────────────
                    Tables\Actions\BulkAction::make('export_pdf_selected')
                        ->label('Export PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('danger')
                        ->form([static::columnForm()])
                        ->action(function (Collection $records, array $data) {
                            $cols      = $data['columns'];
                            $penjualans = PenjualanProduk::with(['karyawan', 'detil.produk', 'pembayaran'])
                                ->whereIn('id_penjualan', $records->pluck('id_penjualan'))
                                ->orderBy('tgl_jual', 'desc')
                                ->get();

                            $pdf = Pdf::loadView('exports.penjualan_produk_pdf', [
                                'penjualans'   => $penjualans,
                                'selectedCols' => $cols,
                                'columnLabels' => array_intersect_key(static::columnOptions(), array_flip($cols)),
                                'rows'         => $penjualans->map(fn ($p) => static::buildRow($p, $cols)),
                                'generated'    => now()->format('d M Y H:i'),
                            ])->setPaper('a4', 'landscape');

                            return response()->streamDownload(
                                fn () => print($pdf->output()),
                                'penjualan-terpilih-' . now()->format('Y-m-d') . '.pdf',
                                ['Content-Type' => 'application/pdf'],
                            );
                        }),

                    // ── Bulk Export CSV ───────────────────────
                    Tables\Actions\BulkAction::make('export_csv_selected')
                        ->label('Export Excel / CSV')
                        ->icon('heroicon-o-table-cells')
                        ->color('success')
                        ->form([static::columnForm()])
                        ->action(function (Collection $records, array $data) {
                            $cols      = $data['columns'];
                            $labels    = array_intersect_key(static::columnOptions(), array_flip($cols));
                            $penjualans = PenjualanProduk::with(['karyawan', 'detil.produk', 'pembayaran'])
                                ->whereIn('id_penjualan', $records->pluck('id_penjualan'))
                                ->orderBy('tgl_jual', 'desc')
                                ->get();

                            return response()->streamDownload(function () use ($penjualans, $cols, $labels) {
                                $handle = fopen('php://output', 'w');
                                fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
                                fputcsv($handle, array_values($labels));
                                foreach ($penjualans as $p) {
                                    fputcsv($handle, array_values(static::buildRow($p, $cols)));
                                }
                                fclose($handle);
                            }, 'penjualan-terpilih-' . now()->format('Y-m-d') . '.csv',
                                ['Content-Type' => 'text/csv; charset=UTF-8']);
                        }),

                    Tables\Actions\DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->emptyStateHeading('Belum ada transaksi penjualan')
            ->emptyStateDescription('Buat transaksi baru dengan klik tombol di atas.');
    }

    // ─────────────────────────────────────────────────────────
    // HELPERS — Column selection (untuk export)
    // ─────────────────────────────────────────────────────────

    public static function columnOptions(): array
    {
        return [
            'id_penjualan'  => 'ID Penjualan',
            'karyawan'      => 'Karyawan',
            'tgl_jual'      => 'Tgl Jual',
            'total_jual'    => 'Total Jual',
            'metode_bayar'  => 'Metode Bayar',
            'total_bayar'   => 'Total Bayar',
            'kembalian'     => 'Kembalian',
            'status_bayar'  => 'Status Bayar',
        ];
    }

    public static function columnForm(): \Filament\Forms\Components\CheckboxList
    {
        return \Filament\Forms\Components\CheckboxList::make('columns')
            ->label('Pilih Kolom yang Diekspor')
            ->options(static::columnOptions())
            ->default(array_keys(static::columnOptions()))
            ->columns(3)
            ->required()
            ->minItems(1)
            ->helperText('Minimal 1 kolom harus dipilih.');
    }

    public static function buildRow(PenjualanProduk $p, array $cols): array
    {
        $all = [
            'id_penjualan' => $p->id_penjualan,
            'karyawan'     => $p->karyawan?->nama ?? '-',
            'tgl_jual'     => $p->tgl_jual?->format('d/m/Y') ?? '-',
            'total_jual'   => 'Rp ' . number_format($p->total_jual, 0, ',', '.'),
            'metode_bayar' => match ($p->pembayaran?->metode_bayar) {
                'tunai'    => 'Tunai',
                'transfer' => 'Transfer',
                'qris'     => 'QRIS',
                default    => '-',
            },
            'total_bayar'  => 'Rp ' . number_format($p->pembayaran?->total_bayar ?? 0, 0, ',', '.'),
            'kembalian'    => 'Rp ' . number_format($p->pembayaran?->kembalian ?? 0, 0, ',', '.'),
            'status_bayar' => ucfirst($p->pembayaran?->status_bayar ?? '-'),
        ];

        $result = [];
        foreach ($cols as $col) {
            if (isset($all[$col])) $result[$col] = $all[$col];
        }
        return $result;
    }

    // ─────────────────────────────────────────────────────────
    // HELPERS — Hitung total
    // ─────────────────────────────────────────────────────────

    private static function hitungTotal(Get $get, Set $set): void
    {
        $items = $get('detil') ?? [];
        $total = collect($items)->sum(fn ($item) => (int) ($item['sub_total'] ?? 0));
        $set('total_jual', $total);
    }

    // Eager load detil agar kolom "Total Item" tidak N+1 query
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['detil', 'karyawan', 'pembayaran']);
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

            'index'  => Pages\ListPenjualanProduks::route('/'),
            'create' => Pages\CreatePenjualanProduk::route('/create'),
            'edit'   => Pages\EditPenjualanProduk::route('/{record}/edit'),
        ];
    }

}
            'index' => Pages\ListPenjualanProduks::route('/'),
            'create' => Pages\CreatePenjualanProduk::route('/create'),
            'edit' => Pages\EditPenjualanProduk::route('/{record}/edit'),
        ];
    }
}
