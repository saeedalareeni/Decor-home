<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('رقم الفاتورة')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('supplier_name')
                    ->label('التاجر / المورد')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('invoice_date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('المبلغ الإجمالي')
                    ->money('ILS')
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label('عدد البنود')
                    ->counts('items')
                    ->sortable(),
            ])
            ->defaultSort('invoice_date', 'desc')
            ->recordActions([
                ViewAction::make(),
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
