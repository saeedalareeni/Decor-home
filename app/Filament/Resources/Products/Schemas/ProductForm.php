<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->label('اسم المنتج'),
                Select::make('type')
                    ->options([
                        'ستائر'  => 'ستائر',
                        'شيفون'  => 'شيفون',
                        'حلق'  => 'حلق',
                        'أبواب اكورديون'  => 'أبواب اكورديون'
                    ])
                    ->required()
                    ->live()
                    ->label('النوع'),
                TextInput::make('cost_price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('ILS')
                    ->label('سعر التكلفة'),
                TextInput::make('sell_price')
                    ->numeric()
                    ->default(0.0)
                    ->prefix('ILS')
                    ->label('سعر البيع'),
                TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->label('المخزون الإجمالي'),
                TextInput::make('supplier_name')
                    ->label('اسم المورد')
                    ->placeholder('أدخل اسم المورد'),

                // إضافة Repeater للألوان
                // Repeater::make('productColor')
                //     ->relationship('ProductColor')
                //     ->label('ألوان المنتج')
                //     ->schema([
                //         TextInput::make('color')
                //             ->label('اللون')
                //             ->required(),

                //         TextInput::make('stock')
                //             ->label('المخزون')
                //             ->numeric()
                //             ->required()
                //             ->default(0),
                //     ])
                //     ->columns(2)
                //     ->collapsible()
                //     ->itemLabel(fn (array $state): ?string => $state['color'] ?? null),
            ]);
    }
}
