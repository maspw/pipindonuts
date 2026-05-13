<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembelianBahanbakuResource\Pages;
use App\Models\PembelianBahanbaku;
use App\Models\Bahan;
use App\Models\Karyawan;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Exports\PembelianBahanbakuExporter;
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
        return $form->schema([
            Wizard::make([
                // STEP 1: DATA PESANAN
                Wizard\Step::make('Pesanan')->schema([
                    TextInput::make('id_pembelian')
                        ->label('Id Pembelian')
                        ->default(fn () => PembelianBahanbaku::generateNoFaktur())
                        ->readOnly(),
                    DateTimePicker::make('tgl_beli')
                        ->label('Tanggal Beli')
                        ->default(now())
                        ->required(),
                    Select::make('id_supplier')
                        ->relationship('supplier', 'nama_supplier')
                        ->label('Supplier')
                        ->required()
                        ->preload(),
                    Select::make('id_karyawan')
                        ->relationship('karyawan', 'nama')
                        ->label('Karyawan')
                        ->required()
                        ->preload(),
                ])->columns(2),

                // STEP 2: PILIH BARANG
                Wizard\Step::make('Pilih Barang')->schema([
                    Repeater::make('detail_pembelian')
                        ->relationship('detail_pembelian')
                        ->schema([
                            Select::make('id_bahanbaku')
                                ->label('Bahan Baku')
                                ->options(Bahan::all()->pluck('nama_bahan', 'id'))
                                ->required()
                                ->columnSpan(2)
                                ->preload(),
                            TextInput::make('harga_satuan')
                                ->label('Harga')
                                ->numeric()
                                ->prefix('Rp')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, $set, $get) => $set('subtotal', $state * $get('jumlah'))),
                            TextInput::make('jumlah')
                                ->label('Qty')
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, $set, $get) => $set('subtotal', $state * $get('harga_satuan'))),
                            TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->numeric()
                                ->prefix('Rp')
                                ->readOnly(),
                        ])->columns(4)->defaultItems(1),
                ]),

                // STEP 3: RINGKASAN & KONFIRMASI
                Wizard\Step::make('Pembayaran')->schema([
                    Placeholder::make('summary')
                        ->label('Ringkasan Transaksi')
                        ->content(function ($get, $record) {
                            $faktur = $get('id_pembelian') ?? $record?->id_pembelian ?? 'PB-XXXXXXX';
                            $tgl = $get('tgl_beli') ?? $record?->tgl_beli ?? now();
                            $total = collect($get('detail_pembelian'))->sum('subtotal');
                            
                            if ($total == 0 && $record) {
                                $total = $record->total_beli;
                            }

                            return view('filament.components.pembelian-table', [
                                'no_faktur' => $faktur,
                                'tgl' => $tgl,
                                'total' => $total,
                            ]);
                        }),

                    Forms\Components\Actions::make([
                        Action::make('confirm_payment')
                            ->label('Bayar') 
                            ->color('success')
                            ->size('md')
                            ->icon('heroicon-m-banknotes')
                            ->requiresConfirmation()
                            ->modalHeading('Konfirmasi Pembayaran')
                            ->modalDescription('Apakah data transaksi sudah benar?')
                            ->modalSubmitActionLabel('Ya, bayar')
                            ->hidden(fn ($operation) => $operation === 'view')
                            ->action(function (Forms\Contracts\HasForms $livewire, $record) {
                                if ($record) {
                                    $livewire->save();
                                } else {
                                    $livewire->create();
                                }
                                
                                Notification::make()
                                    ->title('Transaksi Berhasil')
                                    ->body('Data telah disimpan dan invoice dikirim ke email.')
                                    ->success()
                                    ->send();
                            }),
                    ])->alignCenter(),

                    Hidden::make('total_beli')
                        ->dehydrateStateUsing(fn($get) => collect($get('detail_pembelian'))->sum('subtotal')),
                ]),
            ])->columnSpanFull()
            ->submitAction(new HtmlString('')) 
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_pembelian')
                    ->label('ID Pembelian')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tgl_beli')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier'),
                Tables\Columns\TextColumn::make('total_beli')
                    ->label('Total Harga')
                    ->money('IDR')
                    ->color('success')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('status')
                    ->state(fn() => 'LUNAS')
                    ->badge()
                    ->color('success'),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(PembelianBahanbakuExporter::class)
                    ->label('Export Excel')
                    ->color('success'),

                Tables\Actions\Action::make('unduhPdf')
                    ->label('Unduh PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        $records = PembelianBahanbaku::with(['supplier'])->get();
                        $pdf = Pdf::loadView('pdf.pembelian', ['records' => $records]);
                        return response()->streamDownload(fn () => print($pdf->output()), 'Laporan_Pembelian_Pipindonuts.pdf');
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(PembelianBahanbakuExporter::class)
                        ->label('Export Terpilih'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembelianBahanbakus::route('/'),
            'create' => Pages\CreatePembelianBahanbaku::route('/create'),
            'edit' => Pages\EditPembelianBahanbaku::route('/{record}/edit'),
        ];
    }
}