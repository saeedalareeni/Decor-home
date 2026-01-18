<?php

namespace App\Filament\Resources\Refunds\Schemas;

use App\Models\Refund;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RefundInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('sale.customer.name')
                    ->numeric(),
                TextEntry::make('refund_number'),
                TextEntry::make('total_refund_amount')
                    ->numeric(),
                TextEntry::make('refund_method')
                    ->badge(),
                TextEntry::make('reason')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Refund $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
