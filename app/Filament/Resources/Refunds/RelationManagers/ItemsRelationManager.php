<?php

namespace App\Filament\Resources\Refunds\RelationManagers;

use App\Models\Product;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Factories\Relationship;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship("product", 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $price = Product::find($state)?->selling_price ?? 0;
                        $set('unit_price', $price);
                    }),

                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $get, $set) {
                        $set("total_price", $state * $get('unit_price'));
                    }),

                TextInput::make('unit_price')
                    ->required()
                    ->readOnly()
                    ->prefix('ILS'),

                TextInput::make('total_price')
                    ->required()
                    ->readOnly()
                    ->prefix('ILS'),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('product_id')
                    ->numeric(),
                TextEntry::make('quantity')
                    ->numeric(),
                TextEntry::make('unit_price')
                    ->money(),
                TextEntry::make('total_price')
                    ->money(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('items')
            ->columns([
                TextColumn::make('product.name')
                    ->label("Product")
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->money("ILS")
                    ->sortable(),
                TextColumn::make('total_price')
                    ->money("ILS")
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
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                DeleteAction::make(),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
