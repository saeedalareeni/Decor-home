<?php

namespace App\Filament\Resources\Refunds\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class RefundForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sale_id')
                    ->relationship("sale.customer", 'name')
                    ->label(__("filament.customer"))
                    ->required()
                    ->searchable()
                    ->preload(),

                TextInput::make('refund_number')
                    ->required()
                    ->unique(),

                TextInput::make('total_refund_amount')
                    ->required()
                    ->numeric(),

                Select::make('refund_method')
                    ->options(['cash' => 'Cash', 'card' => 'Card', 'store_credit' => 'Store credit'])
                    ->required(),

                Textarea::make('reason')
                    ->columnSpanFull(),
            ]);
    }
}
