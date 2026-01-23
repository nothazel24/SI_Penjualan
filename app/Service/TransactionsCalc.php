<?php

namespace App\Service;

use App\Models\Product;
use App\Models\PromoCode;

class TransactionsCalc
{
    public static function calculate(
        int $productId,
        int $qty,
        ?int $promoCodeId = null
    ) {
        // get data
        $price = Product::whereKey($productId)->value('price');

        if (!$price || $qty < 1) {
            return [
                'subtotal' => 0,
                'discount' => 0,
                'total' => 0
            ];
        }

        // calculate & store data
        $subtotal = $price * $qty;
        $discount = 0;

        // promo codes section
        if ($promoCodeId) {
            $promo = PromoCode::whereKey($promoCodeId)->first();

            if ($promo) {
                $discount = min($promo->discount_amount, $subtotal);
            }
        }

        // return data
        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $subtotal - $discount
        ];
    }
}
