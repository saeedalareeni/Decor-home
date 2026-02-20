<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الفاتورة')
                    ->description('تفاصيل الفاتورة الواردة من التاجر')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('supplier_name')
                            ->label('اسم التاجر / المورد')
                            ->size('lg')
                            ->weight('bold'),
                        TextEntry::make('invoice_number')
                            ->label('رقم الفاتورة')
                            ->placeholder('—'),
                        TextEntry::make('invoice_date')
                            ->label('تاريخ الفاتورة')
                            ->date('Y-m-d'),
                        TextEntry::make('total_amount')
                            ->label('المبلغ الإجمالي')
                            ->money('ILS')
                            ->size('lg')
                            ->weight('bold'),
                        TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('بنود الفاتورة')
                    ->description('ما تم إدخاله في هذه الفاتورة')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->table([
                                TableColumn::make('المنتج / الوصف'),
                                TableColumn::make('الكمية'),
                                TableColumn::make('سعر الوحدة'),
                                TableColumn::make('الإجمالي'),
                            ])
                            ->schema([
                                TextEntry::make('display_name'),
                                TextEntry::make('quantity')->numeric(decimalPlaces: 2),
                                TextEntry::make('unit_price')->money('ILS'),
                                TextEntry::make('line_total')->money('ILS'),
                            ])
                            ->placeholder('لا توجد بنود'),
                    ]),
            ]);
    }
}
