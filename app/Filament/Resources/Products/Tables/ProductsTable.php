<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Sum;


class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('اسم المنتج')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('الصنف')
                    ->searchable(),
                TextColumn::make('supplier_name')
                    ->label('اسم المورد')
                    ->searchable(),
                TextColumn::make('cost_price')
                    ->label('سعر التكلفة')
                    ->money("ILS")
                    
                    ->sortable(),
                TextColumn::make('sell_price')
                    ->money("ILS")
                    ->label('سعر البيع')
                    ->sortable(),

                TextColumn::make('stock')
                    ->label('الكمية')
                    ->summarize(
                        Sum::make()
                            ->label('المجموع')
                            ->money('ILS', locale: 'en'))
                    ->numeric()
                    ->sortable(),
    TextColumn::make('total_cost_virtual')
    ->label('إجمالي التكلفة')
    ->money('ILS')
    
    ->getStateUsing(fn ($record) => $record->cost_price * $record->stock)
    ->summarize(
        \Filament\Tables\Columns\Summarizers\Summarizer::make()
            ->label('المجموع')
            ->using(fn ($query) => $query->sum(\DB::raw('cost_price * stock')))
            ->prefix('₪ ')
    ),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
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
