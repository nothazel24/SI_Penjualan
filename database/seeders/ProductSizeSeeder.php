<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Product;
use App\Models\ProductSize;

class ProductSizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $product = Product::first();

        if (! $product) {
            return;
        }

        $sizes = [
            ['size' => '35'],
            ['size' => '36'],
            ['size' => '37'],
            ['size' => '38'],
            ['size' => '39'],
            ['size' => '40'],
            ['size' => '41'],
            ['size' => '42'],
            ['size' => '43'],
            ['size' => '44'],
            ['size' => '45'],
        ];

        foreach ($sizes as $size) {
            ProductSize::create([
                'product_id' => $product->id,
                'size'       => $size['size']
            ]);
        }
    }
}
