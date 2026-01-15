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
use Filament\Forms\Components\TagsInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

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
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Produk'),

                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->label('Harga'),

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
                                Repeater::make('gallery')
                                    ->schema([
                                        FileUpload::make('gallery')
                                            ->image()
                                            ->directory('products/gallery')
                                            ->maxSize(1024)
                                            ->label('Tambahkan gambar produk yang lainnya'),
                                    ])
                                    ->addActionLabel('Tambah thumbnail Lainnya')
                                    ->reorderableWithButtons(),

                                Repeater::make('ukuran')
                                    ->schema([
                                        // bug null data
                                        TagsInput::make('size')
                                            ->required()
                                            ->label('Tambahkan ukuran produk yang lainnya')
                                            ->suggestions([
                                                'S',
                                                'M',
                                                'L',
                                                'XL', 
                                                'XXL'
                                            ])
                                    ])
                                    ->addActionLabel('Tambah ukuran produk lainnya'),
                            ])
                            ->columns(2),


                        // Fieldset 3 (Neccessary Information)
                        Fieldset::make('Informasi Tambahan')
                            ->schema([
                                TextInput::make('about')
                                    ->required()
                                    ->label('Deskripsi / Tentang Produk'),

                                Select::make('is_popular')
                                    ->required()
                                    ->label('Produk populer?')
                                    ->options([
                                        '1' => 'Populer',
                                        '0' => 'Tidak'
                                    ]),

                                Select::make('category_id')
                                    ->nullable()
                                    ->relationship('category', 'name') // setting relationship (table name, displayed data(column))
                                    ->label('Kategori Produk'),

                                Select::make('brand_id')
                                    ->nullable()
                                    ->relationship('brand', 'name')
                                    ->label('Brand Produk'),

                                TextInput::make('stock')
                                    ->required()
                                    ->numeric()
                                    ->prefix('pcs')
                                    ->label('Stok Barang'),
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
