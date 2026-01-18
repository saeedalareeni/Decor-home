<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalExpenses extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 2;

    protected function getStats(): array
    {
        $totalExpenses = Expense::sum('amount');

        return [
            Stat::make('إجمالي المصروفات', number_format($totalExpenses, 2) . ' ILS')
                ->icon('heroicon-o-arrow-trending-down')
                ->color('danger'),
        ];
    }
}
