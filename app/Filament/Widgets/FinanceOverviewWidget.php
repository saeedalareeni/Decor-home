<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinanceOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'ملخص مالي';

    protected ?string $description = 'إجمالي الفواتير، المصروفات، وصافي الربح بعد الخصم';

    /** عرض الثلاثة مؤشرات في صف واحد على الشاشات الكبيرة */
    protected int | array | null $columns = ['default' => 1, 'sm' => 2, 'xl' => 3];

    protected function getStats(): array
    {
        $totalInvoices = (float) Invoice::sum('total_amount');
        $totalExpenses = (float) Expense::sum('amount');
        $salesProfit = (float) Sale::sum('profit');
        $netProfit = $salesProfit - $totalInvoices - $totalExpenses;

        return [
            Stat::make('إجمالي الفواتير', $this->formatCurrency($totalInvoices))
                ->description('مجموع قيمة فواتير المحل')
                ->icon('heroicon-o-document-text')
                ->color('info'),

            Stat::make('إجمالي المصروفات', $this->formatCurrency($totalExpenses))
                ->description('مجموع المصروفات المسجلة')
                ->icon('heroicon-o-arrow-trending-down')
                ->color('warning'),

            Stat::make('صافي الربح', $this->formatCurrency($netProfit))
                ->description('أرباح المبيعات − الفواتير − المصروفات')
                ->icon('heroicon-o-currency-dollar')
                ->color($netProfit >= 0 ? 'success' : 'danger'),
        ];
    }

    protected function formatCurrency(float $amount): string
    {
        $formatted = number_format(abs($amount), 2, '.', ',');
        $symbol = $amount < 0 ? '- ' : '';
        return $symbol . $formatted . ' ₪';
    }
}
