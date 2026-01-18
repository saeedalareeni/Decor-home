<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label("الاسم")
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label("التصنيف")
                    ->sortable(),

                TextColumn::make('unit')
                    ->label("الوحدة")
                    ->badge(),

                TextColumn::make('stock')
                    ->label("الكمية")
                    ->numeric()
                    ->sortable(),

                TextColumn::make('cost_price')
                    ->label("سعر التكلفة")
                    ->money("ILS")
                    ->sortable(),

                TextColumn::make('selling_price')
                    ->label("سعر البيع")
                    ->money("ILS")
                    ->sortable(),

                TextColumn::make('deleted_at')
                    ->label("تاريخ الحذف")

                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->label("تاريخ الانشاء")
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label("تاريخ التحديث")
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
