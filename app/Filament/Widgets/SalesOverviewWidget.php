<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverviewWidget extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // إجمالي عدد الفواتير
        $totalSalesCount = Sale::count();

        // إجمالي قيمة المبيعات
        $totalSalesAmount = Sale::sum('total_price') ?? 0;

        // إجمالي التكاليف
        $totalCostAmount = Sale::sum('total_cost') ?? 0;

        // إجمالي الربح
        $totalProfit = Sale::sum('profit') ?? 0;

        // عدد العملاء
        $totalCustomers = Customer::count();

        return [
            Stat::make('إجمالي المبيعات', number_format($totalSalesCount))
                ->description('عدد الفواتير المنجزة')
                ->descriptionIcon('heroicon-m-document-text')
                ->icon('heroicon-o-shopping-bag')
                ->color('info'),

            Stat::make('قيمة المبيعات', $this->formatCurrency($totalSalesAmount))
                ->description($this->getChangePercentage($totalSalesAmount))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make('إجمالي التكاليف', $this->formatCurrency($totalCostAmount))
                ->description('تكلفة المنتجات المباعة')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->icon('heroicon-o-calculator')
                ->color('warning'),

            Stat::make('الربح الإجمالي', $this->formatCurrency($totalProfit))
                ->description($totalProfit >= 0 ? 'نتيجة إيجابية ✓' : 'نتيجة سلبية ✗')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->icon('heroicon-o-chart-pie')
                ->color($totalProfit >= 0 ? 'success' : 'danger'),

            Stat::make('عدد العملاء', number_format($totalCustomers))
                ->description('إجمالي العملاء المسجلين')
                ->descriptionIcon('heroicon-m-users')
                ->icon('heroicon-o-user-group')
                ->color('primary'),
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
     * حساب نسبة التغير
     */
    protected function getChangePercentage(float $amount): string
    {
        if ($amount <= 0) {
            return 'لا توجد مبيعات';
        }
        return 'المبيعات النشطة';
    }
}
