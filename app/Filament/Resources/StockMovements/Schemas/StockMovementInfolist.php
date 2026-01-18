<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;

class StockMovementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make("product.name"),
                TextEntry::make("type"),
                TextEntry::make("quantity"),
                TextEntry::make("reference"),
                TextEntry::make("reference_id"),
            ]);
    }
}
