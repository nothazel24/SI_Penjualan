<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoCodeResource\Pages;
use App\Filament\Resources\PromoCodeResource\RelationManagers;
use App\Models\PromoCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// library or nah...
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class PromoCodeResource extends Resource
{
    protected static ?string $model = PromoCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('code')
                    ->label('Kode Promo')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->rules([
                        'min:8',
                        'max:20'
                    ])
                    ->validationMessages([
                        'unique' => 'Kode promo sudah digunakan. Silahkan masukkan kode promo yang lain',
                        'min' => 'Kode promo terlalu pendek (min 8 karakter)',
                        'max' => 'Kode promo terlalu panjang (max 20 karakter)'
                    ]),

                TextInput::make('discount_amount')
                    ->prefix('IDR')
                    ->label('Diskon Harga')
                    ->required()
                    ->rules([
                        'numeric',
                        'min:20000'
                    ])
                    ->validationMessages([
                        'numeric' => 'Jumlah diskon hanya bisa diisi oleh angka',
                        'min' => 'Jumlah diskon terlalu kecil (min Rp 20.000)'
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Promo')
                    ->searchable(),

                TextColumn::make('discount_amount')
                    ->label('Jumlah diskon')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    // Soft Deletes Restore & delete (permanent)
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListPromoCodes::route('/'),
            'create' => Pages\CreatePromoCode::route('/create'),
            'edit' => Pages\EditPromoCode::route('/{record}/edit'),
        ];
    }

    // Soft Deletes
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
