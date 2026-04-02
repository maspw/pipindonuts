<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Import komponen form
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Group;

// Import komponen tabel
use Filament\Tables\Columns\TextColumn;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Supplier';

    protected static ?string $pluralLabel = 'Data Supplier';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Supplier')
                    ->schema([
                        Group::make()
                            ->schema([
                                TextInput::make('nama_supplier')
                                    ->label('Nama Supplier')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('no_telp')
                                    ->label('No Telepon')
                                    ->tel()
                                    ->required()
                                    ->maxLength(20),
                            ]),

                        Group::make()
                            ->schema([
                                Textarea::make('alamat')
                                    ->label('Alamat')
                                    ->required()
                                    ->rows(5), 
                            ]),
                    ])
                    ->columns(2),

               Section::make('Arsip Dokumen')
                    ->schema([
                        FileUpload::make('dokumen_invoice')
                            ->label('Dokumen Invoice')
                            ->directory('invoice_documents')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_supplier')
                    ->label('Nama Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->limit(30),

                TextColumn::make('no_telp')
                    ->label('No Telepon'),

                TextColumn::make('dokumen_invoice')
                    ->label('Dokumen')
                    // cek file nya ada atau tidak supaya tidak error Not Found
                    ->url(fn($record) => $record->dokumen_invoice ? asset('storage/' . $record->dokumen_invoice) : null, true)
                    ->formatStateUsing(fn($state) => $state 
                        ? '<a href="' . asset('storage/' . $state) . '" target="_blank"><i class="fas fa-file-pdf"></i> 📄 </a>' 
                        : 'Tidak Ada File')
                    ->html(), 

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}