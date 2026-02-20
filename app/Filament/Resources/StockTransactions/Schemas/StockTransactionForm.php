<?php

namespace App\Filament\Resources\StockTransactions\Schemas;

use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\productColor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StockTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('المنتج')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($set) => $set('product_color_id', null)),

                Select::make('product_color_id')
                    ->label('لون المنتج')
                    ->options(function ($get) {
                        $productId = $get('product_id');
                        if (! $productId) {
                            return [];
                        }
                        return productColor::where('product_id', $productId)->pluck('color', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->live(),

                TextInput::make('quantity')
                    ->label('الكمية')
                    ->required()
                    ->numeric()
                    ->minValue(0.001),

                Select::make('type')
                    ->label('نوع الحركة')
                    ->options([
                        StockTransaction::TYPE_IN => 'إدخال',
                        StockTransaction::TYPE_OUT => 'إخراج',
                    ])
                    ->required()
                    ->default(StockTransaction::TYPE_IN),
            ]);
    }
}
