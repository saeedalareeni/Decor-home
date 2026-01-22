<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\Expense;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NetProfitAfterExpenses extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'md';

    protected function getStats(): array
    {
        // حساب إجمالي الربح من المبيعات
        $salesProfit = Sale::sum('profit') ?? 0;

        // حساب إجمالي المصروفات
        $expenses = Expense::sum('amount') ?? 0;

        // صافي الربح بعد المصروفات
        $netProfit = $salesProfit - $expenses;

        return [
            Stat::make('صافي الربح بعد المصروفات', $this->formatCurrency($netProfit))
                ->description($this->getDescription($salesProfit, $expenses))
                ->icon('heroicon-o-currency-dollar')
                ->color($netProfit >= 0 ? 'success' : 'danger')
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
        $formatted = number_format(abs($amount), 2, '.', ',');
        $symbol = $amount < 0 ? '- ' : '';
        return $symbol . $formatted . ' ILS';
    }

    /**
     * وصف تفصيلي للربح
     */
    // protected function getDescription(float $sales, float $expenses): string
    // {
    //     return "الربح: " . $this->formatCurrency($sales) . " | المصروفات: " . $this->formatCurrency($expenses);
    // }
}
