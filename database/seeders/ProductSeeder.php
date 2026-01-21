<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Sepatu Sneakers Hitam',
            'slug' => Str::slug('Sepatu Sneakers Hitam'),
            'thumbnail' => 'images/dummy.jpeg',
            'about' => 'Sepatu sneakers warna hitam dengan desain minimalis dan nyaman digunakan sehari-hari.',
            'price' => 350000,
            'stock' => 20,
            'is_popular' => true,
            'category_id' => 1,
            'brand_id' => 1,
        ]);
    }
}
