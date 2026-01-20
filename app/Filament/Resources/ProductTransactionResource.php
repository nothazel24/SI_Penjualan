<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductTransactionResource\Pages;
use App\Filament\Resources\ProductTransactionResource\RelationManagers;
use App\Models\ProductTransaction;
use App\Service\WilayahService;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
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

class ProductTransactionResource extends Resource
{
    protected static ?string $model = ProductTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // Fieldset 1
                Fieldset::make('Informasi pembeli')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
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

                        // Fieldset 2
                        Fieldset::make('Alamat lengkap pembeli')
                            ->schema([
                                Select::make('province_id')
                                    ->label('Provinsi')
                                    // call function provinces() -> App\Service\WilayahService.php
                                    ->options(fn() => WilayahService::provinces())
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(fn(callable $set) => $set('city_id', null))
                                    ->required(),

                                Select::make('city_id')
                                    ->label('Kota / Kabupaten')
                                    ->options(
                                        // fetching data & result
                                        fn(callable $get) =>
                                        $get('province_id') ? WilayahService::cities($get('province_id')) : []
                                    )
                                    ->searchable()
                                    ->required()
                                    // disable jika tidak ada data provinsi yang terdeteksi
                                    ->disabled(fn(callable $get) => ! $get('province_id')),

                                TextInput::make('post_code')
                                    ->label('Kode Pos')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('address')
                                    ->label('Alamat')
                                    ->required(),
                            ])
                    ]),

                // Fieldset 3
                Fieldset::make('Detail Produk')
                    ->schema([
                        TextInput::make('booking_trx_id')
                            ->label('Booking Transaction ID')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->hiddenOn('create'),

                        Select::make('product_id')
                            ->required()
                            ->relationship('product', 'name')
                            ->label('Produk yang dibeli'),

                        Select::make('size')
                            ->label('Ukuran')
                            ->required()
                            ->options([
                                's' => 'S',
                                'm' => 'M',
                                'l' => 'L',
                                'xl' => 'XL',
                                'xxl' => 'XXL'
                            ]),

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

                        Select::make('promo_code_id')
                            ->nullable()
                            ->relationship('promo_code', 'code')
                            ->label('Kode Promo'),

                        FileUpload::make('proof')
                            ->image()
                            ->directory('products/proof')
                            ->maxSize(1024)
                            ->required()
                            ->columnSpanFull()
                            ->label('Bukti pembelian')
                    ]),

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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
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
            'index' => Pages\ListProductTransactions::route('/'),
            'create' => Pages\CreateProductTransaction::route('/create'),
            'edit' => Pages\EditProductTransaction::route('/{record}/edit'),
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
