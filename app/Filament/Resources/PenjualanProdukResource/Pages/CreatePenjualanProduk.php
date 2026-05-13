<?php

namespace App\Filament\Resources\PenjualanProdukResource\Pages;

use App\Filament\Resources\PenjualanProdukResource;
use App\Models\Karyawan;
use App\Models\PenjualanProduk;
use App\Models\Produk;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

class CreatePenjualanProduk extends CreateRecord
{
    use HasWizard;

    protected static string $resource = PenjualanProdukResource::class;

    protected function getSteps(): array
    {
        return [

            // ── STEP 1: Informasi Transaksi ──────────────────
            Step::make('Informasi Transaksi')
                ->description('Tentukan karyawan dan tanggal penjualan')
                ->icon('heroicon-o-document-text')
                ->schema([

                    TextInput::make('id_penjualan')
                        ->label('ID Penjualan')
                        ->default(fn () => PenjualanProduk::generateIdPublic())
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->helperText('ID dibuat otomatis oleh sistem'),

                    Select::make('karyawan_id')
                        ->label('Karyawan (Kasir)')
                        ->options(
                            Karyawan::all()->mapWithKeys(fn ($k) => [
                                $k->id_karyawan => $k->nama . ' — ' . $k->posisi,
                            ])
                        )
                        ->searchable()
                        ->native(false)
                        ->nullable()
                        ->placeholder('Pilih karyawan (opsional)'),

                    DatePicker::make('tgl_jual')
                        ->label('Tanggal Jual')
                        ->default(now())
                        ->required()
                        ->native(false)
                        ->displayFormat('d M Y'),

                ])
                ->columns(3),

            // ── STEP 2: Pilih Produk ─────────────────────────
            Step::make('Detail Produk')
                ->description('Tambahkan produk yang dijual beserta jumlahnya')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([

                    Repeater::make('detil')
                        ->label('')
                        ->relationship('detil')
                        ->schema([

                            Select::make('produk_id')
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

                            TextInput::make('harga_satuan')
                                ->label('Harga Satuan')
                                ->numeric()
                                ->prefix('Rp')
                                ->readOnly()
                                ->dehydrated()
                                ->columnSpan(2),

                            TextInput::make('jumlah')
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

                            TextInput::make('sub_total')
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

                ])
                ->afterValidation(function (Get $get, Set $set) {
                    self::hitungTotal($get, $set);
                }),

            // ── STEP 3: Pembayaran & Konfirmasi ─────────────
            Step::make('Pembayaran & Konfirmasi')
                ->description('Isi data pembayaran dan konfirmasi transaksi')
                ->icon('heroicon-o-banknotes')
                ->schema([

                    TextInput::make('total_jual')
                        ->label('Total Jual')
                        ->numeric()
                        ->prefix('Rp')
                        ->readOnly()
                        ->dehydrated()
                        ->helperText('Dihitung otomatis dari detail produk'),

                    Fieldset::make('Data Pembayaran')
                        ->relationship('pembayaran')
                        ->schema([

                            Select::make('metode_bayar')
                                ->label('Metode Bayar')
                                ->options([
                                    'tunai'    => '💵 Tunai',
                                    'transfer' => '🏦 Transfer',
                                    'qris'     => '📱 QRIS',
                                ])
                                ->default('tunai')
                                ->required()
                                ->native(false),

                            TextInput::make('total_bayar')
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

                            TextInput::make('kembalian')
                                ->label('Kembalian')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->readOnly()
                                ->dehydrated(),

                            Select::make('status_bayar')
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
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['total_jual'])) {
            $data['total_jual'] = collect($data['detil'] ?? [])
                ->sum(fn ($d) => (int) ($d['sub_total'] ?? 0));
        }
        return $data;
    }

    private static function hitungTotal(Get $get, Set $set): void
    {
        $items = $get('detil') ?? [];
        $total = collect($items)->sum(fn ($item) => (int) ($item['sub_total'] ?? 0));
        $set('total_jual', $total);
    }
}
