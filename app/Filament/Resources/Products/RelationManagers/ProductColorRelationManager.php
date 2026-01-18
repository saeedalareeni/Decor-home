<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductColorRelationManager extends RelationManager
{
    protected static string $relationship = 'ProductColor';

    protected static ?string $modelLabel = "الوان المنتج";
    protected static ?string $pluralLabel = "الوان المنتج";
    
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('color')->label("اللون")
                    ->required()
                    ->maxLength(255),
                TextInput::make('stock')->label("الكمية")
                    ->required()
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('productColor')
            ->columns([
                TextColumn::make('color')->label("اللون"),
                TextColumn::make('stock')->label("الكمية"),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
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
