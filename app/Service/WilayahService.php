<?php

namespace App\Service;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/* 

Cache digunakan disini, guna mempercepat fetching data dari API
dan mengurangi beban komputer. Serta menghindari timeout terlalu lama.

*/

class WilayahService
{
    public static function provinces()
    {
        // menyimpan data dalam cache
        return Cache::remember('provinces', 86400, function () {
            // mengembalikkan data array dengan batas timeout (5) detik
            return Http::timeout(5)
                // fetching data API
                ->get('https://open-api.my.id/api/wilayah/provinces')
                ->collect()
                ->pluck('name', 'id')
                ->toArray();
        });
    }

    public static function cities($provinceId)
    {
        return Cache::remember("cities_{$provinceId}", 86400, function () use ($provinceId) {
            return Http::timeout(5)
                ->get("https://open-api.my.id/api/wilayah/regencies/{$provinceId}")
                ->collect()
                ->pluck('name', 'id')
                ->toArray();
        });
    }
}
