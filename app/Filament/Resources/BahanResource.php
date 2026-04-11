<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BahanResource\Pages;
use App\Models\Bahan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class BahanResource extends Resource
{
    protected static ?string $model = Bahan::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Bahan Baku';

    protected static ?string $modelLabel = 'Bahan Baku';

    protected static ?string $pluralModelLabel = 'Bahan Baku';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Bahan')
                    ->description('Data identitas bahan baku')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\TextInput::make('nama_bahan')
                            ->label('Nama Bahan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Tepung Terigu'),

                        Forms\Components\Select::make('satuan')
                            ->label('Satuan')
                            ->options([
                                'kg'    => 'Kilogram (kg)',
                                'gr'    => 'Gram (gr)',
                                'liter' => 'Liter (L)',
                                'ml'    => 'Mililiter (ml)',
                                'pcs'   => 'Pcs / Buah',
                                'pack'  => 'Pack',
                                'dus'   => 'Dus / Karton',
                            ])
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Stok & Batas Minimum')
                    ->description('Kelola jumlah stok dan batas peringatan minimum')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Forms\Components\TextInput::make('stok_qty')
                            ->label('Stok Saat Ini')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix(fn ($get) => $get('satuan') ?? '')
                            ->required(),

                        Forms\Components\TextInput::make('stok_minimum')
                            ->label('Stok Minimum (Alert)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix(fn ($get) => $get('satuan') ?? '')
                            ->helperText('Stok akan ditandai merah jika di bawah nilai ini')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Tanggal Kadaluarsa')
                    ->description('Kosongkan jika bahan tidak memiliki tanggal kadaluarsa')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Forms\Components\DatePicker::make('tgl_exp')
                            ->label('Tanggal Kadaluarsa')
                            ->native(false)
                            ->displayFormat('d M Y'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_bahan')
                    ->label('Nama Bahan')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('satuan')
                    ->label('Satuan')
                    ->badge()
                    ->color(fn (string $state) => match(true) {
                        in_array($state, ['kg', 'gr'])           => 'info',
                        in_array($state, ['liter', 'ml'])        => 'success',
                        in_array($state, ['pcs', 'pack', 'dus']) => 'warning',
                        default                                  => 'gray',
                    }),

                Tables\Columns\TextColumn::make('stok_qty')
                    ->label('Stok')
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $state . ' ' . $record->satuan)
                    ->color(fn ($state, $record) => $state <= $record->stok_minimum ? 'danger' : 'success')
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('stok_minimum')
                    ->label('Min. Stok')
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $state . ' ' . $record->satuan)
                    ->color('gray'),

                Tables\Columns\TextColumn::make('tgl_exp')
                    ->label('Kadaluarsa')
                    ->date('d M Y')
                    ->sortable()
                    ->default('—')
                    ->color(fn ($state, $record) => match(true) {
                        !$record->tgl_exp                                       => 'gray',
                        $record->tgl_exp->isPast()                              => 'danger',
                        $record->tgl_exp->diffInDays(now(), true) <= 7          => 'warning',
                        default                                                 => 'success',
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->date('d M Y')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nama_bahan')
            ->filters([
                SelectFilter::make('satuan')
                    ->label('Filter Satuan')
                    ->options([
                        'kg'    => 'Kilogram (kg)',
                        'gr'    => 'Gram (gr)',
                        'liter' => 'Liter (L)',
                        'ml'    => 'Mililiter (ml)',
                        'pcs'   => 'Pcs / Buah',
                        'pack'  => 'Pack',
                        'dus'   => 'Dus / Karton',
                    ])
                    ->native(false),

                Tables\Filters\Filter::make('stok_menipis')
                    ->label('Stok Menipis')
                    ->query(fn (Builder $query) => $query->whereColumn('stok_qty', '<=', 'stok_minimum')),

                Tables\Filters\Filter::make('hampir_kadaluarsa')
                    ->label('Hampir / Sudah Kadaluarsa')
                    ->query(fn (Builder $query) => $query
                        ->whereNotNull('tgl_exp')
                        ->where('tgl_exp', '<=', now()->addDays(7))
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus yang Dipilih'),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-archive-box')
            ->emptyStateHeading('Belum ada bahan baku')
            ->emptyStateDescription('Tambahkan bahan baku pertama untuk mulai mengelola stok.');
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
            'index'  => Pages\ListBahans::route('/'),
            'create' => Pages\CreateBahan::route('/create'),
            'edit'   => Pages\EditBahan::route('/{record}/edit'),
        ];
    }
}
