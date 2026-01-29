<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SalesStatsOne extends BaseWidget
{
    protected function getStats(): array
    {
        // ambil data transaksi yang sudah lunas, dan hitung
        $total = DB::table('product_transactions')
            ->where('is_paid', true)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('grand_total_amount');

        // get total (produk yang terjual)
        $totalProducts = DB::table('product_transactions')
            ->where('is_paid', true)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('qty');

        return [
            // pendapatan bulanan
            Stat::make('Pendapatan bulan ini', 'Rp ' . number_format($total, 0, ',', '.'))
                ->description('Transaksi yang Lunas')
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            // Produk yang terjual
            Stat::make('Produk yang terjual bulan ini', $totalProducts . ' pcs')
                ->description('Transaksi yang Lunas')
                ->icon('heroicon-o-shopping-bag')
                ->color('success'),
        ];
    }

    // set grid
    protected function getColumns(): int
    {
        return 2;
    }
}
