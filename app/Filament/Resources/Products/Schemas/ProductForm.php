<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label("الاسم")
                    ->required(),
                Select::make('category_id')
                    ->relationShip("category", "name")
                    ->label("التصنيف")
                    ->searchable()
                    ->preload() 
                    ->required(),
                Select::make('unit')
                    ->label("النوع")

                    ->options(['piece' => 'Piece', 'meter' => 'Meter'])
                    ->required(),
                TextInput::make('stock')
                    ->required()
                    ->label("المخزون")

                    ->numeric()
                    ->default(0.0),
                TextInput::make('cost_price')
                    ->required()
                    ->label("سعر التكلفة")

                    ->numeric()
                    ->prefix('ILS'),
                TextInput::make('selling_price')
                    ->required()
                    ->label("سعر البيع")

                    ->numeric()
                    ->prefix('ILS'),
            ]);
    }
}
