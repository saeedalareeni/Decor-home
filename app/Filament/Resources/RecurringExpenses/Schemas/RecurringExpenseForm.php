<?php

namespace App\Filament\Resources\RecurringExpenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RecurringExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label("الاسم")
                    ->required(),
                TextInput::make('amount')
                    ->label("المبلغ")

                    ->required()
                    ->numeric(),
                Select::make('frequency')
                    ->label("دوري")

                    ->options(['monthly' => 'شهري', 'weekly' => 'اسبوعي', 'daily' => 'يومي'])
                    ->required(),
                DatePicker::make('start_date')
                    ->label("تاريخ البداية")

                    ->required(),
                DatePicker::make('end_date')
                ->label("تاريخ النهاية")
                ,
            ]);
    }
}
