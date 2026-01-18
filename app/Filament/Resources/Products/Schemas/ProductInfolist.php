<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('category.name')
                    ->label("التصنيف")
                    ->numeric(),
                TextEntry::make('name')
                    ->label("الاسم"),
                TextEntry::make('unit')
                    ->label("النوع")

                    ->badge(),
                TextEntry::make('stock')
                    ->label("الكمية")

                    ->numeric(),
                TextEntry::make('cost_price')
                    ->label("سعر التكلفة")

                    ->money("ILS"),
                TextEntry::make('selling_price')
                    ->label("سعر البيع")

                    ->money("ILS"),
                TextEntry::make('deleted_at')
                    ->label("تاريخ الحذف")

                    ->dateTime()
                    ->visible(fn(Product $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->label("تاريخ الانشاء")

                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label("تاريخ التعديل")

                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
