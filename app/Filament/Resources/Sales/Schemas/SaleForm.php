<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->label('الزبون'),

                DatePicker::make('sale_date')
                    ->label('تاريخ البيع'),

                TextInput::make('total_price')
                    ->label('إجمالي سعر البيع')
                    ->disabled(),

                TextInput::make('total_cost')
                    ->label('إجمالي التكلفة')
                    ->disabled(),

                TextInput::make('profit')
                    ->label('الربح')
                    ->disabled(),
            ]);
    }
}
