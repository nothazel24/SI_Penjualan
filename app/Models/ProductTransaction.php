<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use function Symfony\Component\Clock\now;

class ProductTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'booking_trx_id',
        'province_id',
        'city_id',
        'post_code',
        'address',
        'size',
        'sub_total_amount',
        'grand_total_amount',
        'is_paid',
        'product_id',
        'promo_code_id',
        'proof'
    ];

    // Auto generate booking_id coyy
    protected static function booted()
    {
        static::creating(function ($order) {
            $order->booking_trx_id = 'TRX' . '-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function promo_code()
    {
        return $this->belongsTo(PromoCode::class, 'promo_code_id');
    }
}
