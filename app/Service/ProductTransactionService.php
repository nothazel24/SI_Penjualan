<?php

namespace App\Service;

use App\Models\Product;
use App\Models\ProductTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductTransactionService
{
    // create process
    public function create(array $data): ProductTransaction
    {
        return DB::transaction(function () use ($data) {

            // cari data
            $product = Product::lockForUpdate()
                ->findOrFail($data['product_id']);

            // validate qty dengan stock yang tersedia di produk
            if ($data['qty'] > $product->stock) {
                throw ValidationException::withMessages([
                    'qty' => 'Stok tidak mencukupi.',
                ]);
            }

            $product->decrement('stock', $data['qty']);

            return ProductTransaction::create($data);
        });
    }

    // update process
    public function update(ProductTransaction $transaction, array $data): ProductTransaction
    {
        return DB::transaction(function () use ($transaction, $data) {

            $product = Product::lockForUpdate()
                ->findOrFail($data['product_id']);

            // hitung SELISIH qty
            $oldQty = $transaction->qty;
            $newQty = $data['qty'];
            $diff   = $newQty - $oldQty;

            // kalau qty bertambah → cek stok
            if ($diff > 0 && $diff > $product->stock) {
                throw ValidationException::withMessages([
                    'qty' => 'Stok tidak mencukupi.',
                ]);
            }

            // update stok berdasarkan selisih
            if ($diff !== 0) {
                $product->decrement('stock', $diff);
            }

            $transaction->update($data);

            return $transaction;
        });
    }
}
