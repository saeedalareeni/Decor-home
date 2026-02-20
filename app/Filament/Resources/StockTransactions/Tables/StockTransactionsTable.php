<?php

namespace App\Filament\Resources\StockTransactions\Tables;

use App\Models\StockTransaction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class StockTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('المنتج')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('productColor.color')
                    ->label('اللون')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('type')
                    ->label('النوع')
                    ->formatStateUsing(fn (string $state) => $state === StockTransaction::TYPE_IN ? 'إدخال' : 'إخراج')
                    ->badge()
                    ->color(fn (string $state) => $state === StockTransaction::TYPE_IN ? 'success' : 'danger'),
                TextColumn::make('reference_type')
                    ->label('مرجع')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('month_year')
                    ->label('فلترة شهرية / سنوية')
                    ->form([
                        Select::make('month')
                            ->label('الشهر')
                            ->options([
                                '1'  => 'يناير',
                                '2'  => 'فبراير',
                                '3'  => 'مارس',
                                '4'  => 'أبريل',
                                '5'  => 'مايو',
                                '6'  => 'يونيو',
                                '7'  => 'يوليو',
                                '8'  => 'أغسطس',
                                '9'  => 'سبتمبر',
                                '10' => 'أكتوبر',
                                '11' => 'نوفمبر',
                                '12' => 'ديسمبر',
                            ]),
                        Select::make('year')
                            ->label('السنة')
                            ->options(
                                collect(range(now()->year, now()->year - 5))
                                    ->mapWithKeys(fn ($year) => [$year => $year])
                                    ->toArray()
                            ),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['month'] ?? null,
                                fn ($q) => $q->whereMonth('created_at', $data['month'])
                            )
                            ->when(
                                $data['year'] ?? null,
                                fn ($q) => $q->whereYear('created_at', $data['year'])
                            );
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
