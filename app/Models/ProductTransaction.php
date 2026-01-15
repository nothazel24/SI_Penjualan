<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'booking_trx_id',
        'city',
        'post_code',
        'address',
        'quantity',
        'sub_total_amount',
        'grand_total_amount',
        'is_paid',
        'product_id',
        'product_size',
        'promo_code_id',
        'proof'
    ];

    // Generate unique code
    public static function generateUniqueTrxId()
    {
        $prefix = 'TJH';
        do {
            $randomString = $prefix . mt_rand(10001, 99999);
        } while (self::where('booking_trx_id', $randomString)->exist());

        return $randomString;
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class, 'promo_code_id');
    }
}
