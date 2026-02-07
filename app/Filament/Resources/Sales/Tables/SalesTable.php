<?php

namespace App\Filament\Resources\Sales\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('customer.name')->label('الزبون')->searchable(),
                TextColumn::make('sale_date')->label('التاريخ'),
                TextColumn::make('total_price')->label('سعر البيع')
                ->summarize(
                        Sum::make()
                            ->label('المجموع')
                            ->money('ILS', locale: 'en')),
                TextColumn::make('total_cost')->label('التكلفة')
                ->summarize(
                        Sum::make()
                            ->label('المجموع')
                            ->money('ILS', locale: 'en')),
                TextColumn::make('profit')->label('الربح')
                ->summarize(
                        Sum::make()
                            ->label('المجموع')
                            ->money('ILS', locale: 'en')),

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
                Filter::make('month_year')
                    ->label('فلترة شهرية')
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
                                    ->mapWithKeys(fn($year) => [$year => $year])
                                    ->toArray()
                            ),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['month'],
                                fn($q) => $q->whereMonth('created_at', $data['month'])
                            )
                            ->when(
                                $data['year'],
                                fn($q) => $q->whereYear('created_at', $data['year'])
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
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
