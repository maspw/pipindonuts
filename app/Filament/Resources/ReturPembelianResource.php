<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturPembelianResource\Pages;
use App\Models\Bahan;
use App\Models\DetilPembelian;
use App\Models\Karyawan;
use App\Models\PembelianBahanbaku;
use App\Models\ReturPembelian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReturPembelianResource extends Resource
{
    protected static ?string $model = ReturPembelian::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Retur Pembelian';

    protected static ?string $modelLabel = 'Retur Pembelian';

    protected static ?string $pluralModelLabel = 'Retur Pembelian Bahan Baku';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Retur')
                    ->description('Informasi retur bahan baku ke supplier')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->schema([
                        Forms\Components\Select::make('pembelian_id')
                            ->label('Pembelian Asal')
                            ->options(
                                PembelianBahanbaku::with('supplier')
                                    ->get()
                                    ->mapWithKeys(fn ($p) => [
                                        $p->id => '#' . $p->id . ' — ' . ($p->supplier?->nama_supplier ?? '?') . ' (' . $p->tgl_beli?->format('d M Y') . ')',
                                    ])
                            )
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, $set) => $set('bahan_id', null)),

                        Forms\Components\Select::make('bahan_id')
                            ->label('Bahan yang Diretur')
                            ->options(function (Get $get) {
                                $pembelianId = $get('pembelian_id');
                                if (!$pembelianId) return [];
                                return DetilPembelian::where('pembelian_id', $pembelianId)
                                    ->with('bahan')
                                    ->get()
                                    ->mapWithKeys(fn ($d) => [
                                        $d->bahan_id => $d->bahan?->nama_bahan . ' (' . $d->jumlah . ' ' . $d->bahan?->satuan . ')',
                                    ]);
                            })
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->live()
                            ->helperText('Pilih pembelian asal terlebih dahulu'),

                        Forms\Components\Select::make('karyawan_id')
                            ->label('Karyawan (Pemroses)')
                            ->options(
                                Karyawan::all()->mapWithKeys(fn ($k) => [
                                    $k->id_karyawan => $k->nama . ' (' . $k->posisi . ')',
                                ])
                            )
                            ->required()
                            ->searchable()
                            ->native(false),

                        Forms\Components\DatePicker::make('tgl_retur')
                            ->label('Tanggal Retur')
                            ->required()
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->default(now()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Retur')
                    ->description('Informasi jenis, jumlah, dan status retur')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Forms\Components\Select::make('tipe_retur')
                            ->label('Tipe Retur')
                            ->options([
                                'rusak'       => '🔴 Rusak / Cacat',
                                'salah_kirim' => '🟡 Salah Kirim',
                                'kelebihan'   => '🔵 Kelebihan Stok',
                                'lainnya'     => '⚪ Lainnya',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('jumlah')
                            ->label('Jumlah Diretur')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->suffix(function (Get $get) {
                                $bahanId = $get('bahan_id');
                                if (!$bahanId) return '';
                                return Bahan::find($bahanId)?->satuan ?? '';
                            }),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending'   => 'Pending',
                                'disetujui' => 'Disetujui',
                                'ditolak'   => 'Ditolak',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('alasan')
                            ->label('Alasan Retur')
                            ->rows(3)
                            ->placeholder('Jelaskan alasan retur...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pembelian.supplier.nama_supplier')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('bahan.nama_bahan')
                    ->label('Bahan Diretur')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tipe_retur')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'rusak'       => 'danger',
                        'salah_kirim' => 'warning',
                        'kelebihan'   => 'info',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'rusak'       => 'Rusak',
                        'salah_kirim' => 'Salah Kirim',
                        'kelebihan'   => 'Kelebihan',
                        default       => 'Lainnya',
                    }),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state, $record) => $state . ' ' . ($record->bahan?->satuan ?? '')),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'disetujui' => 'success',
                        'ditolak'   => 'danger',
                        default     => 'warning',
                    }),

                Tables\Columns\TextColumn::make('tgl_retur')
                    ->label('Tgl Retur')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Diproses Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tgl_retur', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'disetujui' => 'Disetujui',
                        'ditolak'   => 'Ditolak',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('tipe_retur')
                    ->label('Tipe Retur')
                    ->options([
                        'rusak'       => 'Rusak',
                        'salah_kirim' => 'Salah Kirim',
                        'kelebihan'   => 'Kelebihan',
                        'lainnya'     => 'Lainnya',
                    ])
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Edit'),
                Tables\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus yang Dipilih'),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-arrow-uturn-left')
            ->emptyStateHeading('Belum ada retur pembelian')
            ->emptyStateDescription('Catat retur bahan baku pertama dengan klik tombol di atas.');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListReturPembelians::route('/'),
            'create' => Pages\CreateReturPembelian::route('/create'),
            'edit'   => Pages\EditReturPembelian::route('/{record}/edit'),
        ];
    }
}
