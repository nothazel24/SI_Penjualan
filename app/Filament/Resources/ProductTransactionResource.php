<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductTransactionResource\Pages;
use App\Filament\Resources\ProductTransactionResource\RelationManagers;
use App\Models\ProductTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// Library or nahh..
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

use Illuminate\Support\Facades\Http;

class ProductTransactionResource extends Resource
{
    protected static ?string $model = ProductTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Pembeli'),

                TextInput::make('email')
                    ->required()
                    ->maxLength(255)
                    ->label('Email Pengguna'),

                TextInput::make('phone')
                    ->required()
                    ->tel()
                    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                    // custom validation messages
                    ->validationMessages([
                        'required' => 'Nomor telepon wajib diisi!',
                        'regex' => 'Nomor hanya bisa diisi oleh angka!'
                    ])
                    ->label('Nomor Telepon'),

                TextInput::make('booking_trx_id')
                    ->label('Booking Transaction ID')
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit'),

                // bikin database baru & perbaharui (Product Transaction)
                Select::make('province_id')
                    ->label('Kota / Kabupaten')
                    ->options(function () {
                        return Http::get(
                            'https://open-api.my.id/api/wilayah/provinces'
                        )
                            ->collect()
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn(callable $set) => $set('city_id', null))
                    ->required(),

                Select::make('city_id')
                    ->label('Kota / Kabupaten')
                    ->options(function (callable $get) {
                        if (! $get('province_id')) {
                            return [];
                        }

                        return Http::get(
                            "https://open-api.my.id/api/wilayah/regencies/{$get('province_id')}"
                        )
                            ->collect()
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->disabled(fn(callable $get) => ! $get('province_id')),

                TextInput::make('post_code')
                    ->label('Kode Pos')
                    ->numeric()
                    ->required(),

                FileUpload::make('proof')
                    ->image()
                    ->directory('products/proof')
                    ->maxSize(1024)
                    ->required()
                    ->label('Bukti'),

                TextInput::make('size')
                    ->label('Ukuran')
                    ->required()
                    ->numeric(),

                TextInput::make('address')
                    ->label('Alamat')
                    ->required(),

                TextInput::make('sub_total_amount')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->label('Sub Total'),

                TextInput::make('grand_total_amount')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->label('Grand Total'),

                Select::make('is_paid')
                    ->required()
                    ->label('Sudah Lunas?')
                    ->options([
                        '1' => 'Sudah Lunas',
                        '0' => 'Belum Lunas'
                    ]),

                Select::make('product_id')
                    ->required()
                    ->relationship('product', 'name')
                    ->label('Produk'),

                Select::make('promo_code_id')
                    ->required()
                    ->relationship('promo_code', 'code')
                    ->label('Kode Promo'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Pengguna'),

                TextColumn::make('product.name')
                    ->label('Produk yang dibeli'),

                TextColumn::make('email')
                    ->label('Email'),

                TextColumn::make('phone')
                    ->label('Nomor Telepon'),

                TextColumn::make('booking_trx_id')
                    ->label('Booking ID'),

                IconColumn::make('is_paid')
                    ->boolean()
                    ->label('Status Pembayaran'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductTransactions::route('/'),
            'create' => Pages\CreateProductTransaction::route('/create'),
            'edit' => Pages\EditProductTransaction::route('/{record}/edit'),
        ];
    }
}
