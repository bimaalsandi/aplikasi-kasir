<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    public static ?int $sort = 1;
    protected function getStats(): array
    {
        return [
            Stat::make('Total Produk', Product::count())
                ->description('Produk aktif tersedia')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),

            Stat::make('Total Transaksi', Transaction::count())
                ->description('Jumlah transaksi berhasil')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('Pendapatan', 'Rp ' . number_format(Transaction::sum('total_amount'), 0, ',', '.'))
                ->description('Total pemasukan kotor')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
}
