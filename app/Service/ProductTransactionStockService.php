<?php

namespace App\Service;

use App\Models\Product;
use App\Models\ProductTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductTransactionStockService
{
    // restore stock product ke semula (jika transaksi dihapus)
    public function restoreStock(ProductTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            // get data
            $product = Product::lockForUpdate()
                ->find($transaction->product_id);

            // kembalikan stock ke semula (nilai saat stock diambil oleh produk)
            if ($product) {
                // penambahan stock + qty
                $product->increment('stock', $transaction->qty);
            }
        });
    }

    // restore stock
    public function deductStock(ProductTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            // get data
            $product = Product::lockForUpdate()
                ->find($transaction->product_id);

            if (! $product) {
                return;
            }

            // jika qty tidak mencukupi untuk melakukan restore
            if ($transaction->qty > $product->stock) {
                throw ValidationException::withMessages([
                    // throw notification
                    'qty' => 'Stok tidak mencukupi untuk melakukan restore transaksi',
                ]);
            }

            // pengurangan stock - qty
            $product->decrement('stock', $transaction->qty);
        });
    }
}
