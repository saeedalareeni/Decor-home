<?php

namespace App\Filament\Resources\RecurringExpenses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RecurringExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                ->label("الاسم")
                    ->searchable(),
                TextColumn::make('amount')
                ->label("المبلغ")

                    ->numeric()
                    ->sortable(),
                    TextColumn::make('frequency')
                    ->label("الفتره")
                    ->badge(),
                TextColumn::make('start_date')
                ->label("تاريخ البداية")

                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                ->label("تاريخ النهاية")
                    ->date()
                    ->sortable(),
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
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
