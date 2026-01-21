<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSize extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'size',
        'product_id'
    ];

    // Auto get size data (dari relasi bersarang tabel product_size)
    protected static function getSizes(?int $productId)
    {
        // checking
        if (!$productId) {
            return [];
        }

        // get data
        return static::where('product_id', $productId)
            ->pluck('size', 'id')
            ->toArray();
    }

    public function product_size()
    {
        return $this->belongsTo(ProductSize::class);
    }
}
