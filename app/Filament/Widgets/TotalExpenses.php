<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalExpenses extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'md';

    protected function getStats(): array
    {
        $totalExpenses = Expense::sum('amount') ?? 0;
        $expenseCount = Expense::count();

        return [
            Stat::make('إجمالي المصروفات', $this->formatCurrency($totalExpenses))
                ->description("عدد المصروفات: " . $expenseCount)
                ->icon('heroicon-o-arrow-trending-down')
                ->color('danger')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),
        ];
    }

    /**
     * تنسيق العملة بشكل احترافي
     */
    protected function formatCurrency(float $amount): string
    {
        return number_format($amount, 2, '.', ',') . ' ILS';
    }
}
