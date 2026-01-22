<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SalesProfitChart extends ChartWidget
{
    protected ?string $heading = 'إجمالي المبيعات وصافي الربح';

    protected int | string | array $columnSpan = 'md';

    protected function getData(): array
    {
        $data = Sale::query()
            ->select(
                DB::raw('SUM(total_price) as total_sales'),
                DB::raw('SUM(profit) as total_profit'),
                DB::raw('SUM(total_cost) as total_cost')
            )
            ->first();

        $totalSales = (float) ($data->total_sales ?? 0);
        $totalProfit = (float) ($data->total_profit ?? 0);
        $totalCost = (float) ($data->total_cost ?? 0);

        return [
            'labels' => ['إجمالي المبيعات', 'إجمالي التكاليف', 'صافي الربح'],
            'datasets' => [
                [
                    'label' => 'القيم (ILS)',
                    'data' => [
                        $totalSales,
                        $totalCost,
                        $totalProfit,
                    ],
                    'backgroundColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(239, 68, 68)',
                        'rgb(34, 197, 94)',
                    ],
                    'borderColor' => [
                        'rgb(29, 78, 216)',
                        'rgb(220, 38, 38)',
                        'rgb(22, 163, 74)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'responsive' => true,
            'maintainAspectRatio' => true,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'enabled' => true,
                    'callbacks' => [
                        'label' => "function(context) { return context.raw.toFixed(2) + ' ILS'; }",
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
