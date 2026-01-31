<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// library or nahh..
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ActionGroup;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Fieldset Grouping btw.
                Fieldset::make('Informasi Produk')
                    ->schema([
                        TextInput::make('name')
                            ->unique(ignoreRecord: true)
                            ->label('Nama Produk')
                            ->required()
                            ->rules([
                                'string',
                                'min:5',
                                'max:255'
                            ])
                            ->validationMessages([
                                'unique' => 'Nama produk sudah ada',
                                'min' => 'Nama produk terlalu pendek (min 5 karakter)',
                                'max' => 'Nama produk terlalu panjang'
                            ]),

                        TextInput::make('price')
                            ->prefix('Rp')
                            ->label('Harga')
                            ->required()
                            ->rules([
                                /*
                                    kenapa pakai rules bang? gak pake method filament yang required(), numeric(), sama minValue(100000)?. simple aja, pengen custom messages sy.
                                */
                                'numeric',
                                'min:100000'
                            ])
                            ->validationMessages([
                                'required' => 'Harga produk harus diisi',
                                'numeric' => 'Harga hanya bisa diisi oleh angka',
                                'min' => 'Harga yang dimasukkan terlalu rendah (min Rp. 100.000)'
                            ]),

                        // Primary thumbnail
                        FileUpload::make('thumbnail')
                            ->image()
                            ->directory('products')
                            ->maxSize(1024)
                            ->required()
                            ->columnSpanFull() // full width form input
                            ->label('Thumbnail'),

                        // Fieldset 2
                        FieldSet::make('')
                            ->schema([
                                // Repeatable thumbnail
                                Repeater::make('photos')
                                    ->relationship()
                                    ->schema([
                                        FileUpload::make('photo')
                                            ->image()
                                            ->nullable()
                                            ->directory('products/photos')
                                            ->maxSize(1024)
                                            ->label('Tambahkan gambar produk yang lainnya'),
                                    ])
                                    ->addActionLabel('Tambah thumbnail Lainnya')
                                    ->reorderableWithButtons()
                                    ->label('Foto Tambahan'),

                                Repeater::make('sizes') // hasMany Relationship ('sizes')
                                    ->relationship()
                                    ->schema([
                                        Select::make('size')
                                            ->label('Tambahkan ukuran produk yang lainnya')
                                            ->options(
                                                // looping ukuran/size (30-45)
                                                collect(range(30, 45))
                                                    ->mapWithKeys(fn($size) => [$size => (string) $size])
                                                    ->toArray()
                                            )
                                            ->distinct() // checking antar item repeater agar tidak ada data duplikat 
                                            ->required()
                                            ->validationMessages([
                                                'distinct' => 'Ukuran tidak boleh duplikat'
                                            ])
                                    ])
                                    ->addActionLabel('Tambah ukuran produk lainnya')
                                    ->label('Ukuran'),
                            ])
                            ->columns(2),


                        // Fieldset 3 (Neccessary Information)
                        Fieldset::make('Informasi Tambahan')
                            ->schema([
                                Textarea::make('about')
                                    ->label('Deskripsi / Tentang Produk')
                                    ->required()
                                    ->rules([
                                        'min:5',
                                        'max:500'
                                    ])
                                    ->validationMessages([
                                        'min' => 'Deskripsi produk terlalu pendek (min 5 karakter)',
                                        'max' => 'Deskripsi produk terlalu panjang'
                                    ]),

                                Select::make('is_popular')
                                    ->label('Produk populer?')
                                    ->options([
                                        '1' => 'Populer',
                                        '0' => 'Tidak'
                                    ])
                                    ->required(),

                                Select::make('category_id')
                                    ->required()
                                    ->relationship('category', 'name') // setting relationship (table name, displayed data(column))
                                    ->label('Kategori Produk'),

                                Select::make('brand_id')
                                    ->required()
                                    ->relationship('brand', 'name')
                                    ->label('Brand Produk'),

                                TextInput::make('stock')
                                    ->prefix('pcs')
                                    ->label('Stok Barang')
                                    ->required()
                                    ->rules([
                                        'numeric',
                                    ])
                                    ->validationMessages([
                                        'numeric' => 'Stock produk hanya bisa diisi oleh angka'
                                    ]),
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Thumbnail'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama produk'),

                TextColumn::make('price')
                    ->sortable()
                    ->money('IDR')
                    ->label('Harga'),

                // displaying relationships data (table.columns)
                TextColumn::make('category.name')
                    ->sortable()
                    ->label('Kategori'),

                TextColumn::make('brand.name')
                    ->sortable()
                    ->label('Merk'),

                TextColumn::make('stock')
                    ->sortable()
                    ->label('Stok'),

                IconColumn::make('is_popular')
                    ->boolean()
                    ->label('Populer'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                ])
                ->tooltip('Actions')
                ->icon('heroicon-m-ellipsis-horizontal')
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
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
