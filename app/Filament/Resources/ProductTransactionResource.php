<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductTransactionResource\Pages;
use App\Filament\Resources\ProductTransactionResource\RelationManagers;
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
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

// models
use App\Models\ProductTransaction;
use App\Models\ProductSize;
use Filament\Notifications\Notification;
use Throwable;

class ProductTransactionResource extends Resource
{
    protected static ?string $model = ProductTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // CUSTOMER DATA SECTION
                Section::make('Informasi Pembeli')
                    ->collapsible()
                    ->collapsed()
                    ->columns(2)
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

                        // Fieldset 1
                        Fieldset::make('Alamat lengkap pembeli')
                            ->schema([
                                Select::make('province_id')
                                    ->label('Provinsi')
                                    // call function provinces() -> App\Service\WilayahService.php
                                    ->options(fn() => WilayahService::provinces())
                                    ->searchable()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(callable $set) => $set('city_id', null))
                                    ->required(),

                                Select::make('city_id')
                                    ->label('Kota / Kabupaten')
                                    ->options(function (callable $get) {
                                        // catch data
                                        try {
                                            // App\Service\WilayahService::cities()
                                            return $get('province_id') ? WilayahService::cities($get('province_id')) : [];
                                        } catch (\Throwable) { // trhow notificationnnn
                                            Notification::make()
                                                ->title('API Timeout :(')
                                                ->body('Sepertinya jaringanmu sedang kurang baik, coba refresh dan isi lagi datanya')
                                                ->danger()
                                                ->send();

                                            return [];
                                        }
                                    })
                                    ->searchable()
                                    ->requiredIf('province_id', fn($state) => filled($state))
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
                Section::make('Detail Produk')
                    ->collapsible()
                    ->collapsed()
                    ->columns(2)
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
                            ->reactive()
                            ->label('Produk yang dibeli'),

                        Select::make('size')
                            ->label('Ukuran')
                            ->required() // rawan bug, tapi gpp. nanti fix aja kalo beneran ada, hehe
                            // App/Models/ProductSize::getSize()
                            ->options(function (callable $get) {
                                // catch data
                                try {
                                    return ProductSize::getSizes($get('product_id'));
                                } catch (\Throwable) { // send notification (jika gagal)
                                    Notification::make()
                                        ->title('Masukkan data yang benar!')
                                        ->body('Data tidak boleh kosong, atau bukan angka!')
                                        ->danger()
                                        ->send();

                                    return [];
                                }
                            })
                            ->disabled(fn(callable $get) => !$get('product_id')),

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
                            ->label('Bukti pembelian'),

                        // TOTAL SECTION
                        TextInput::make('sub_total_amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->label('Sub Total'),

                        TextInput::make('grand_total_amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->label('Total'),
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

                // coba search, dibagian boolean ieu bisa diubah nu asal na icons jadi text?
                IconColumn::make('is_paid')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->label('Status Pembayaran'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton(),
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
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
