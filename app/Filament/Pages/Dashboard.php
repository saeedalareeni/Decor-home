<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'الرئيسية';

    protected static ?string $title = 'لوحة التحكم';

    /**
     * شبكة الودجات: عمود واحد لعرض منظم (كل ودجة بعرض كامل).
     */
    public function getColumns(): int | array
    {
        return 1;
    }
}
