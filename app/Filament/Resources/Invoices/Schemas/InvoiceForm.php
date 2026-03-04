<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('supplier_name')
                    ->label('اسم التاجر / المورد')
                    ->required()
                    ->maxLength(255),
                TextInput::make('invoice_number')
                    ->label('رقم الفاتورة')
                    ->maxLength(255),
                DatePicker::make('invoice_date')
                    ->label('تاريخ الفاتورة')
                    ->required()
                    ->default(now()),
                TextInput::make('total_amount')
                    ->label('المبلغ الإجمالي للفاتورة')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->minValue(0),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3),
            ]);
    }
}
