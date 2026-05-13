<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->required()
                ->placeholder('Nama Lengkap Crew'), 
            Forms\Components\TextInput::make('email')
                ->email()
                ->required(),
            Forms\Components\TextInput::make('password')
                ->password()
                ->required(fn (string $context): bool => $context === 'create') 
                ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
            Forms\Components\Select::make('user_group')
                ->options([
                    'Admin' => 'Administrator',
                    'Kasir' => 'Kasir/Crew',
                ])
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(), 
            Tables\Columns\TextColumn::make('email'),
            Tables\Columns\TextColumn::make('user_group')
                ->label('Role')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'Admin' => 'danger', 

                    'Kasir' => 'success',

                    'Kasir' => 'success', 
                    default => 'gray',
                }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_group')
                ->options([
                    'Admin' => 'Admin',
                    'Kasir' => 'Kasir',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
