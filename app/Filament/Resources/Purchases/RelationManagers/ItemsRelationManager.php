<?php

namespace App\Filament\Resources\Purchases\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function getModelLabel(): string
    {
        return __("filament.purchases");
    }

    public static function getPluralLabel(): ?string
    {
        return __("filament.purchases");
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->label(__('filament.name'))
                    ->required(),

                TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->label(__('filament.quantity')),

                TextInput::make('cost_price')
                    ->numeric()
                    ->prefix('ILS')
                    ->required()
                    ->label(__("filament.cost_price")),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Purchase_items')
            ->columns([
                TextColumn::make('product.name')->label(__('filament.name')),
                TextColumn::make('quantity')->label(__('filament.quantity')),
                TextColumn::make('cost_price')->money('ILS',locale:"en")->label(__('filament.cost_price')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
