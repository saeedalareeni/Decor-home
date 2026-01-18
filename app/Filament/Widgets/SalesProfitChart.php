<?php

namespace App\Filament\Widgets;

use App\Models\Sale_item;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SalesProfitChart extends ChartWidget
{
    protected ?string $heading = 'إجمالي المبيعات وصافي الربح';

        protected int | string | array $columnSpan = "md";


    protected function getData(): array
    {
        $data = Sale_item::query()
            ->select(
                DB::raw('SUM(total_price) as total_sales'),
                DB::raw('SUM(net_profit) as total_profit')
            )
            ->first();

        return [
            'labels' => ['إجمالي المبيعات', 'صافي الربح'],
            'datasets' => [
                [
                    'data' => [
                        (float) $data->total_sales,
                        (float) $data->total_profit,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
