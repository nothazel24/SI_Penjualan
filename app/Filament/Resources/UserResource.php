<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Kelola Akun';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->columnSpanFull()
                    ->label('Nama lengkap')
                    ->required()
                    ->regex('/^[A-Za-z\s.]+$/')
                    ->rules([
                        'min:10',
                        'max:45',
                    ])
                    ->validationMessages([
                        'regex' => 'Nama hanya bisa diisi oleh huruf',
                        'min' => 'Nama terlalu pendek. Isi dengan nama lengkap',
                        'max' => 'Nama terlalu panjang'
                    ]),

                TextInput::make('email')
                    ->label('Email Pengguna')
                    ->required()
                    ->regex('/^.+@.+$/i')
                    ->rules([
                        'max:255',
                    ])
                    ->validationMessages([
                        'max' => 'Email terlalu panjang'
                    ]),

                TextInput::make('password')
                    ->label('Password pengguna')
                    ->password()
                    ->revealable(),

                // role akun
                Select::make('role')
                    ->label('Role Akun')
                    ->options([
                        'user' => 'User',
                        'kasir' => 'Kasir',
                        'admin' => 'Admin'
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama pengguna')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email pengguna')
                    ->sortable(),
                TextColumn::make('role')
                    ->label('Role')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->color(fn(string $state): string => match ($state) {
                        'user' => 'info',
                        'kasir' => 'warning',
                        'admin' => 'success',
                        default => 'gray',
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                ])
                    ->tooltip('Actions')
                    ->icon('heroicon-m-ellipsis-horizontal')
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
            //
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
