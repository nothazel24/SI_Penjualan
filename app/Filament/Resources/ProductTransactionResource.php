<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductTransactionResource\Pages;
use App\Filament\Resources\ProductTransactionResource\RelationManagers;
use App\Service\WilayahService;
use App\Service\TransactionsCalc;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// models
use App\Models\ProductTransaction;
use App\Models\ProductSize;
use App\Models\Product;

// Library or nahh..
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;

use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Enums\ActionsPosition;
use Barryvdh\DomPDF\Facade\Pdf;
use Throwable;

class ProductTransactionResource extends Resource
{
    protected static ?string $model = ProductTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    // helper recalculate (Biar gk ngulang terus)
    protected static function recalculate(callable $get, callable $set)
    {
        if (! $get('product_id') || ! $get('qty')) {
            $set('sub_total_amount', 0);
            $set('grand_total_amount', 0);
            return;
        }

        $result = TransactionsCalc::calculate(
            (int) $get('product_id'),
            (int) $get('qty'),
            filled($get('promo_code_id'))
                ? (int) $get('promo_code_id')
                : null
        );

        $set('sub_total_amount', $result['subtotal']);
        $set('grand_total_amount', $result['total']);
    }

    // helper untuk membawa data stock dan menyompannya di memori livewire
    protected static function getQty($state, callable $set)
    {
        $product = Product::find($state);
        $set('stock', $product ? (int) $product->stock : null);
    }

    // helper validate qty
    protected static function validateQtyAgainstStock($qty, callable $get)
    {
        // stopp kalau tipe data masih belum valid (string)
        if (! is_numeric($qty)) {
            return;
        }
        $qty = (int) $qty; // set to int

        // ngambil dari data stock yang sudah diambil & disimpan oleh helper getQty
        $stock = $get('stock');

        if (! is_numeric($stock)) {
            return;
        }

        if ($qty > $stock) {
            Notification::make()
                ->title('Over Quantity')
                ->body('Qty melebihi batas dari stok yang ada!. Silahkan kurangi')
                ->danger()
                ->send();
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // CUSTOMER DATA SECTION
                Fieldset::make('Informasi Pembeli')
                    ->schema([
                        TextInput::make('name')
                            ->columnSpanFull()
                            ->label('Nama Pembeli')
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

                        TextInput::make('phone')
                            ->required()
                            ->rules([
                                'regex:/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/',
                                'min:10',
                                'max:13'
                            ])
                            // custom validation messages
                            ->validationMessages([
                                'regex' => 'Nomor telepon hanya bisa diisi oleh angka tanpa simbol',
                                'min' => 'Nomor telepon terlalu pendek (min 10 digit)',
                                'max' => 'Nomor telepon terlalu panjang (max 13 digit)'
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
                                    ->reactive()
                                    ->afterStateUpdated(fn(callable $set) => $set('city_id', null))
                                    ->required(),

                                Select::make('city_id')
                                    ->label('Kota / Kabupaten')
                                    ->options(function (callable $get) {
                                        // catch data
                                        try {
                                            // App\Service\WilayahService::cities()
                                            return WilayahService::cities($get('province_id'));
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
                                    ->required()
                                    ->rules([
                                        'numeric',
                                        'digits:5'
                                    ])
                                    ->validationMessages([
                                        'numeric' => 'Kode pos hanya bisa diisi oleh angka',
                                        'digits' => 'Kode pos harus terdiri dari 5 angka',
                                    ]),

                                TextInput::make('address')
                                    ->label('Alamat')
                                    ->required()
                                    ->rules([
                                        'min:5',
                                        'max:255'
                                    ])
                                    ->validationMessages([
                                        'min' => 'Alamat rumah terlalu pendek (min 5 karakter)',
                                        'max' => 'Alamat rumah terlalu panjang'
                                    ]),
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
                            ->reactive()
                            ->label('Produk yang dibeli')
                            ->afterStateUpdated(
                                function ($state, callable $get, callable $set) {
                                    static::recalculate($get, $set);
                                    static::getQty($state, $set); // call helper getQty product
                                }
                            ),

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
                                        ->title('Data Produk kosong')
                                        ->body('Produk tidak boleh kosong, silahkan diisi!')
                                        ->danger()
                                        ->send();

                                    return [];
                                }
                            })
                            ->disabled(fn(callable $get) => !$get('product_id')),

                        /*
                            include trigger event function buat relcalculate & validate Qty
                            (jika sudah diupdate (AfterStateUpdated))
                        */
                        TextInput::make('qty')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->reactive()
                            ->live(debounce: 500)
                            ->required()
                            ->afterStateUpdated(
                                function ($state, callable $get, callable $set) {
                                    static::recalculate($get, $set);
                                    static::validateQtyAgainstStock($state, $get);
                                }
                            )
                            ->disabled(fn(callable $get) => !$get('product_id')),

                        // validasi via filament (field bottom message)
                        // ->rule(function (callable $get) { // rule buat check data
                        //     return function ($attribute, $value, $fail) use ($get) {
                        //         $stock = $get('stock');

                        //         if (is_numeric($stock) && $value > (int) $get('stock')) {
                        //             $fail('Qty melebihi stok yang tersedia.');
                        //         }
                        //     };
                        // }),

                        Select::make('is_paid')
                            ->required()
                            ->label('Sudah Lunas?')
                            ->options([
                                '1' => 'Sudah Lunas',
                                '0' => 'Belum Lunas'
                            ])
                            ->disabled(fn(callable $get) => !$get('product_id')),
                        /*
                                perbaikan fitur (method disabled langsung tereksekusi sebelum data masuk kedalam database)
                                EXPECTED RESULT : 
                                method disabled langsung diterapkan JIKA data sudah masuk kedalam database terlebih dahulu
                            */
                        // ->disabled(fn(callable $get) => $get('is_paid') && filled($get('proof'))),

                        Select::make('promo_code_id')
                            ->nullable()
                            ->relationship('promo_code', 'code')
                            ->reactive()
                            ->label('Kode Promo')
                            ->afterStateUpdated(
                                fn($state, callable $set, callable $get) =>
                                static::recalculate($get, $set)
                            )
                            ->disabled(fn(callable $get) => !$get('product_id')),

                        FileUpload::make('proof')
                            ->image()
                            ->nullable()
                            ->directory('products/proof')
                            ->maxSize(1024)
                            ->columnSpanFull()
                            ->label('Bukti pembelian') // tambahin logika disabled juga seperti field is_paid
                            ->disabled(fn(callable $get) => !$get('product_id')),

                        /* 
                            hindari data truncated (dipotong karena berbeda tipe dengan tabel database)
                            saat mengedit transaksi, namun tidak mengubah jumlah dari produk
                        */
                        Hidden::make('sub_total_amount')
                            ->dehydrated(),

                        Hidden::make('grand_total_amount')
                            ->dehydrated(),

                        // total display section
                        Placeholder::make('sub_total_display')
                            ->label('Sub Total')
                            ->content(
                                fn(callable $get) =>
                                'Rp. ' . number_format(
                                    // ambil data dari sub_total_amount
                                    (int) $get('sub_total_amount'),
                                    0,
                                    ',',
                                    ','
                                )
                            ),

                        Placeholder::make('grand_total_display')
                            ->label('Total Harga')
                            ->content(
                                fn(callable $get) =>
                                'Rp. ' . number_format(
                                    (int) $get('grand_total_amount'),
                                    0,
                                    ',',
                                    ','
                                )
                            )
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

                TextColumn::make('is_paid')
                    ->badge()
                    ->label('Status Pembayaran')
                    ->formatStateUsing(fn(bool $state) => $state ? 'Lunas' : 'Belum lunas')
                    ->color(fn(bool $state) => $state ? 'success' : 'danger')
                    ->icon(fn(bool $state) => $state ? 'heroicon-o-check-badge' : 'heroicon-o-x-circle')
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('approve')
                        ->label('Setujui Transaksi')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (ProductTransaction $record) {
                            // validasiii (jika belum ada bukti/proof)
                            if (blank($record->proof)) {

                                // lempar notifikasi
                                Notification::make()
                                    ->title('Persetujuan transaksi ditunda')
                                    ->body('Silahkan isi bukti pembayaran atas nama ' . $record->name)
                                    ->warning()
                                    ->send();

                                // redirect ke halaman edit berdasarkan ID
                                return redirect()->to(
                                    \App\Filament\Resources\ProductTransactionResource::getUrl('edit', [
                                        'record' => $record->id,
                                        'approve' => 1 // tanda bahwa user re-direct dari list dengan menggunakan tombol approve
                                    ])
                                );
                            }

                            // jika proof sudah ada
                            $record->update(['is_paid' => true]);

                            Notification::make()
                                ->title('Transaksi berhasil')
                                ->body('Transaksi atas nama ' . $record->name . ' berhasil disetujui')
                                ->success()
                                ->send();
                        })
                        // ada ketika transaksi statusnya belum lunas, dan tidak ada jika sudah lunas
                        ->visible(fn(ProductTransaction $record) => $record->is_paid == false),

                    // approve section
                    Tables\Actions\Action::make('download_proof')
                        ->label('Download bukti')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->openUrlInNewTab()
                        ->url(fn(ProductTransaction $record) => $record->proof ? Storage::url($record->proof) : null),

                    // download invoice section
                    Tables\Actions\Action::make('download_invoice')
                        ->label('Download Invoice')
                        ->color('warning')
                        ->icon('heroicon-o-folder-arrow-down')
                        ->url(fn($record) => route('invoice.download', $record->id)) // sending record id to route
                        ->openUrlInNewTab() // (!) butuh le, soalna pake url() buat generate pdf invoice
                ])
                    ->tooltip('Actions')
                    ->icon('heroicon-m-ellipsis-horizontal')
            ], ActionsPosition::BeforeColumns)
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
