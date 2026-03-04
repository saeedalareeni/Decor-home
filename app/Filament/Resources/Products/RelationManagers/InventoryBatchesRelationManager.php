<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventoryBatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'inventoryBatches';

    protected static ?string $title = 'دفعات المخزون';

    protected static ?string $pluralLabel = 'دفعات المخزون';

    protected static ?string $modelLabel = 'دفعة';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('received_at')
                    ->label('تاريخ الاستلام')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('cost_price')
                    ->label('سعر الجملة')
                    ->money('ILS')
                    ->sortable(),

                TextColumn::make('productColor.color')
                    ->label('لون المنتج')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('quantity_in')
                    ->label('الكمية الواردة')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('quantity_remaining')
                    ->label('المتبقي')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->color(fn ($record) => (float) $record->quantity_remaining > 0 ? 'success' : 'danger'),

                TextColumn::make('supplier_display')
                    ->label('اسم المورد')
                    ->getStateUsing(function ($record) {
                        if ($record->invoiceItem?->invoice?->supplier_name) {
                            return $record->invoiceItem->invoice->supplier_name;
                        }
                        return $record->product?->supplier_name ?? '—';
                    })
                    ->placeholder('—'),

                TextColumn::make('invoice_item_id')
                    ->label('رقم الفاتورة')
                    ->getStateUsing(fn ($record) => $record->invoiceItem?->invoice?->invoice_number ?? 'دفعة أولية'),
            ])
            ->defaultSort('received_at', 'desc')
            ->headerActions([])
            ->recordActions([])
            ->bulkActions([]);
    }
}
