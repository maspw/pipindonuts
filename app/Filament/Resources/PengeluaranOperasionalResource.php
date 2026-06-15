<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengeluaranOperasionalResource\Pages;
use App\Models\PengeluaranOperasional;
use App\Models\Karyawan;
use App\Mail\PengeluaranOperasionalMail;
use App\Exports\PengeluaranOperasionalExport;

use Illuminate\Support\Facades\Mail;

use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Get;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;

use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class PengeluaranOperasionalResource extends Resource
{
    protected static ?string $model = PengeluaranOperasional::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Pengeluaran Operasional';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Wizard::make([

                    // STEP 1
                    Step::make('Data Pengeluaran')
                        ->schema([

                            Grid::make(2)
                                ->schema([

                                    TextInput::make('id_pengeluaran')
                                        ->label('ID Pengeluaran')
                                        ->default(function () {

                                            $last = PengeluaranOperasional::latest()->first(); //data terakhir

                                            if (!$last) {
                                                return 'PG001';
                                            }

                                            $number = (int) substr($last->id_pengeluaran, 2);

                                            $number++;

                                            return 'PG' . str_pad($number, 3, '0', STR_PAD_LEFT);
                                        })
                                        ->disabled()//field
                                        ->dehydrated()  //data tetap disimpan ke db
                                        ->required(), //data wajib diisi dan tidak boleh null

                                    DatePicker::make('tanggal')
                                        ->required(),

                                ]),

                        ]),

                    // STEP 2
                    Step::make('Data Karyawan')
                        ->schema([

                            Grid::make(2)//buat bagi 2 kolom scr horizontal
                                ->schema([//naruh isi/komponen apa aja yang di grid (tata letak)

                                    Select::make('id_karyawan')
                                        ->label('ID Karyawan')
                                        ->relationship(
                                            name: 'karyawan',
                                            titleAttribute: 'id_karyawan'
                                        )
                                        ->getOptionLabelFromRecordUsing(
                                            fn ($record) =>
                                            $record->id_karyawan . ' - ' . $record->nama
                                        )
                                        ->searchable()
                                        ->preload()//data langsung dimuat
                                        ->live()
                                        ->required(),

                                    TextInput::make('nama_karyawan')
                                        ->label('Nama Karyawan')
                                        ->disabled()
                                        ->dehydrated(false)

                                        ->afterStateHydrated(function ($component, Get $get) { //jalan saat form pertama kali dibuka (menampilkan nama otomatis)

                                            $karyawan = Karyawan::where( //melakukan query ke db tabel karyawan
                                                'id_karyawan',
                                                $get('id_karyawan')//mengambil nilai id karyawan yang dipilih
                                            )->first(); //mengambil satu baris pertama yang cocok

                                            if ($karyawan) {
                                                $component->state($karyawan->nama); //sistem mengisi kolom kode dengan nama karyawan
                                            }
                                        })

                                        ->afterStateUpdated(function ($component, Get $get) {//berjalan saat karyawan diganti

                                            $karyawan = Karyawan::where(
                                                'id_karyawan',
                                                $get('id_karyawan')
                                            )->first();

                                            if ($karyawan) {
                                                $component->state($karyawan->nama);
                                            }
                                        }),

                                ]),

                            \Filament\Forms\Components\Actions::make([

                                \Filament\Forms\Components\Actions\Action::make('proses')
                                    ->label('Proses')
                                    ->color('success')
                                    ->icon('heroicon-o-check-circle')
                                    ->action(function () {

                                        \Filament\Notifications\Notification::make()
                                            ->title('Data berhasil diproses')
                                            ->success()
                                            ->send();

                                    }),

                            ])

                        ]),

                    // STEP 3
                    Step::make('Detail Pengeluaran')
                        ->schema([

                            Grid::make(2)
                                ->schema([

                                    TextInput::make('nama_pengeluaran')
                                        ->required(),

                                    TextInput::make('nominal')
                                        ->label('Nominal')
                                        ->required(),

                                    Select::make('status')
                                        ->options([

                                            'Bayar' => 'Bayar',

                                            'Kredit' => 'Kredit / Belum Bayar',

                                        ])
                                        ->default('Kredit')
                                        ->required(),

                                ]),

                            Textarea::make('keterangan')//input catatan tambahan
                                ->rows(4)
                                ->columnSpanFull(),

                        ]),

                ])


                    ->columnSpanFull()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->columns([

                TextColumn::make('id_pengeluaran')
                    ->label('ID Pengeluaran')
                    ->searchable()
                    ->sortable(),//bisa diurutkan

                TextColumn::make('karyawan.id_karyawan')
                    ->label('ID Karyawan')
                    ->searchable(),

                TextColumn::make('karyawan.nama')
                    ->label('Nama Karyawan')
                    ->searchable(),

                TextColumn::make('tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('nama_pengeluaran')
                    ->searchable(),

                TextColumn::make('nominal')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()//label mempercantik tampilan status
                    ->color(fn (string $state): string => match ($state) {

                        'Bayar' => 'success',

                        'Kredit' => 'danger',

                        default => 'gray',

                    }),

                TextColumn::make('keterangan')
                    ->limit(30),//membatasi jumlah karaternya

            ])

            ->filters([
                //
            ])

            // BULK ACTIONS
            ->bulkActions([//action buat banyak data sekaligus

    Tables\Actions\BulkActionGroup::make([

       // EMAIL
Tables\Actions\BulkAction::make('Email')

    ->icon('heroicon-o-envelope')

    ->color('warning')

    ->action(function ($records) {

        foreach ($records as $record) { //buat ngeloop semua data yang dicentang

            Mail::to(
                'triafrisadarmayani@gmail.com'
            )->send(
                new PengeluaranOperasionalMail($record)
            );

            // DELAY
            sleep(5);//Supaya Mailtrap tidak error “Too many emails per second”

        }

        \Filament\Notifications\Notification::make()
            ->title('Email berhasil dikirim')
            ->success()
            ->send();

    }),

        // EXPORT PDF
        Tables\Actions\BulkAction::make('Export PDF')

            ->icon('heroicon-o-document')

            ->color('danger')

            ->action(function ($records) {

                $pdf = Pdf::loadView(//mengubah blade menjadi pdf
                    'pdf.pengeluaran-operasional',
                    ['data' => $records]
                );

                return response()->streamDownload(//download file pdf
                    fn () => print($pdf->output()),
                    'pengeluaran-operasional.pdf'
                );
            }),

        // EXPORT EXCEL
        Tables\Actions\BulkAction::make('Export Excel')

            ->icon('heroicon-o-table-cells')

            ->color('success')

            ->action(function ($records) {

                return Excel::download(//download file excel
                    new PengeluaranOperasionalExport($records),
                    'pengeluaran-operasional.xlsx'
                );
            }),

        // DELETE
        Tables\Actions\DeleteBulkAction::make(),

    ])

])

            // ACTIONS SAMPING
            ->actions([

                // VIEW
                Tables\Actions\ViewAction::make(),

                // EDIT
                Tables\Actions\EditAction::make(),

                // DELETE
                Tables\Actions\DeleteAction::make(),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [

            'index' => Pages\ListPengeluaranOperasionals::route('/'),

            'create' => Pages\CreatePengeluaranOperasional::route('/create'),

            'edit' => Pages\EditPengeluaranOperasional::route('/{record}/edit'),

        ];
    }
}