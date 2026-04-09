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

                Tables\Columns\BadgeColumn::make('satuan')
                    ->label('Satuan')
                    ->colors([
                        'info'    => fn ($state) => in_array($state, ['kg', 'gr']),
                        'success' => fn ($state) => in_array($state, ['liter', 'ml']),
                        'warning' => fn ($state) => in_array($state, ['pcs', 'pack', 'dus']),
                    ]),

                Tables\Columns\TextColumn::make('stok_qty')
                    ->label('Stok')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->stok_qty . ' ' . $record->satuan)
                    ->color(fn ($record) => $record->stok_qty <= $record->stok_minimum ? 'danger' : 'success')
                    ->icon(fn ($record) => $record->stok_qty <= $record->stok_minimum
                        ? 'heroicon-o-exclamation-triangle'
                        : 'heroicon-o-check-circle'
                    )
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('stok_minimum')
                    ->label('Min. Stok')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->stok_minimum . ' ' . $record->satuan)
                    ->color('gray'),

                Tables\Columns\TextColumn::make('tgl_exp')
                    ->label('Kadaluarsa')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('—')
                    ->color(function ($record) {
                        if (!$record->tgl_exp) return 'gray';
                        if ($record->tgl_exp->isPast()) return 'danger';
                        if ($record->tgl_exp->diffInDays(now()) <= 7) return 'warning';
                        return 'success';
                    })
                    ->icon(function ($record) {
                        if (!$record->tgl_exp) return null;
                        if ($record->tgl_exp->isPast()) return 'heroicon-o-x-circle';
                        if ($record->tgl_exp->diffInDays(now()) <= 7) return 'heroicon-o-clock';
                        return null;
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nama_bahan')
            ->filters([
                SelectFilter::make('satuan')
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
                    ->native(false),

                Tables\Filters\Filter::make('stok_menipis')
                    ->label('Stok Menipis')
                    ->query(fn (Builder $query) => $query->whereColumn('stok_qty', '<=', 'stok_minimum'))
                    ->toggle(),

                Tables\Filters\Filter::make('hampir_kadaluarsa')
                    ->label('Hampir / Sudah Kadaluarsa')
                    ->query(fn (Builder $query) => $query
                        ->whereNotNull('tgl_exp')
                        ->where('tgl_exp', '<=', now()->addDays(7))
                    )
                    ->toggle(),
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
