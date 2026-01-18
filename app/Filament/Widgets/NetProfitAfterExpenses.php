<?php

namespace App\Filament\Widgets;

use App\Models\SaleItem;
use App\Models\Expense;
use App\Models\Sale_item;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NetProfitAfterExpenses extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 2;

    protected function getStats(): array
    {
        $salesProfit = Sale_item::sum('net_profit');
        $expenses    = Expense::sum('amount');

        $netProfit = $salesProfit - $expenses;

        return [
            Stat::make('صافي الربح بعد المصروفات', number_format($netProfit, 2) . ' ILS')
                ->icon('heroicon-o-currency-dollar')
                ->color($netProfit >= 0 ? 'success' : 'danger'),
        ];
    }
}
