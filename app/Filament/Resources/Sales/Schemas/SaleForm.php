<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label("الزبون")
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('payment_method')
                    ->label("طريقة الدفع")

                    ->options(['cash' => 'كاش', 'card' => 'بطاقة صراف', 'transfer' => 'تحويل بنكي'])
                    ->required(),
            ]);
    }
}
