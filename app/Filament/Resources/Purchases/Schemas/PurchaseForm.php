<?php

namespace App\Filament\Resources\Purchases\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('supplier_name')
                    ->label(__("filament.supplier_name"))
                    ->required(),
                TextInput::make('total_amount')
                    ->label(__("filament.total_amount"))
                    ->required()
                    ->numeric(),
            ]);
    }
}
