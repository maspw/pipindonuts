<?php

namespace App\Filament\Resources\ReturPembelianResource\Pages;

use App\Filament\Resources\ReturPembelianResource;
use App\Models\Bahan;
use App\Models\DetilPembelian;
use App\Models\Karyawan;
use App\Models\PembelianBahanbaku;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

class CreateReturPembelian extends CreateRecord
{
    use HasWizard;

    protected static string $resource = ReturPembelianResource::class;

    protected function getSteps(): array
    {
        return [
            // ── STEP 1 ──────────────────────────────────────
            Step::make('Pilih Pembelian')
                ->description('Tentukan pembelian bahan baku yang akan diretur')
                ->icon('heroicon-o-shopping-cart')
                ->schema([
                    Select::make('pembelian_id')
                        ->label('Pembelian Asal')
                        ->options(
                            PembelianBahanbaku::with('supplier')
                                ->get()
                                ->mapWithKeys(fn ($p) => [
                                    $p->id => '#' . $p->id
                                        . ' — ' . ($p->supplier?->nama_supplier ?? '?')
                                        . ' (' . $p->tgl_beli?->format('d M Y') . ')'
                                        . ' | Total: Rp ' . number_format($p->total_beli, 0, ',', '.'),
                                ])
                        )
                        ->required()
                        ->searchable()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(fn ($set) => $set('bahan_id', null))
                        ->helperText('Pilih nota pembelian dari supplier yang bahannya ingin diretur'),
                ]),

            // ── STEP 2 ──────────────────────────────────────
            Step::make('Detail Retur')
                ->description('Isi bahan, tipe, jumlah, dan alasan retur')
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([
                    Select::make('bahan_id')
                        ->label('Bahan yang Diretur')
                        ->options(function (Get $get) {
                            $pembelianId = $get('pembelian_id');
                            if (!$pembelianId) return [];
                            return DetilPembelian::where('pembelian_id', $pembelianId)
                                ->with('bahan')
                                ->get()
                                ->mapWithKeys(fn ($d) => [
                                    $d->bahan_id => ($d->bahan?->nama_bahan ?? '?')
                                        . ' — ' . $d->jumlah . ' ' . ($d->bahan?->satuan ?? ''),
                                ]);
                        })
                        ->required()
                        ->searchable()
                        ->native(false)
                        ->live()
                        ->helperText(fn (Get $get) => !$get('pembelian_id')
                            ? '⚠️ Kembali ke langkah 1 dan pilih pembelian terlebih dahulu'
                            : 'Hanya bahan dari pembelian yang dipilih yang ditampilkan'),

                    Select::make('tipe_retur')
                        ->label('Tipe Retur')
                        ->options([
                            'rusak'       => '🔴 Rusak / Cacat',
                            'salah_kirim' => '🟡 Salah Kirim',
                            'kelebihan'   => '🔵 Kelebihan Stok',
                            'lainnya'     => '⚪ Lainnya',
                        ])
                        ->required()
                        ->native(false),

                    TextInput::make('jumlah')
                        ->label('Jumlah Diretur')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->suffix(fn (Get $get) => Bahan::find($get('bahan_id'))?->satuan ?? ''),

                    Textarea::make('alasan')
                        ->label('Alasan Retur')
                        ->rows(3)
                        ->placeholder('Jelaskan alasan retur secara detail...')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            // ── STEP 3 ──────────────────────────────────────
            Step::make('Status & Konfirmasi')
                ->description('Tentukan karyawan pemroses dan status akhir retur')
                ->icon('heroicon-o-check-circle')
                ->schema([
                    Select::make('karyawan_id')
                        ->label('Karyawan (Pemroses)')
                        ->options(
                            Karyawan::all()->mapWithKeys(fn ($k) => [
                                $k->id_karyawan => $k->nama . ' (' . $k->posisi . ')',
                            ])
                        )
                        ->required()
                        ->searchable()
                        ->native(false),

                    DatePicker::make('tgl_retur')
                        ->label('Tanggal Retur')
                        ->required()
                        ->native(false)
                        ->displayFormat('d M Y')
                        ->default(now()),

                    Select::make('status')
                        ->label('Status Retur')
                        ->options([
                            'pending'   => '⏳ Pending — menunggu persetujuan',
                            'disetujui' => '✅ Disetujui — stok bahan langsung berkurang',
                            'ditolak'   => '❌ Ditolak',
                        ])
                        ->default('pending')
                        ->required()
                        ->native(false)
                        ->helperText('Jika "Disetujui", stok bahan akan otomatis berkurang via Observer.')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

