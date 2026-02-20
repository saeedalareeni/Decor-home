<?php

namespace App\Filament\Resources\Invoices\RelationManagers;

use App\Models\Product;
use App\Models\productColor;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
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

    protected static ?string $title = 'بنود الفاتورة';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('المنتج')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn ($set) => $set('product_color_id', null)),

                Select::make('product_color_id')
                    ->label('لون المنتج')
                    ->options(fn ($get) => $get('product_id')
                        ? productColor::where('product_id', $get('product_id'))->pluck('color', 'id')
                        : [])
                    ->searchable()
                    ->preload(),

                TextInput::make('description')
                    ->label('وصف البند (إن لم تختر منتج)')
                    ->placeholder('مثلاً: مصاريف نقل')
                    ->required(fn ($get) => ! $get('product_id'))
                    ->visible(fn ($get) => ! $get('product_id')),

                TextInput::make('quantity')
                    ->label('الكمية')
                    ->required()
                    ->numeric()
                    ->minValue(0.001)
                    ->default(1),

                TextInput::make('line_total')
                    ->label('المبلغ الإجمالي للبند')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->helperText('أدخل إجمالي المبلغ لهذا المنتج في الفاتورة'),
            ]);
    }

    protected static function lineTotalToUnitPrice(array $data): array
    {
        $qty = (float) ($data['quantity'] ?? 1);
        $total = (float) ($data['line_total'] ?? 0);
        $data['unit_price'] = $qty > 0 ? round($total / $qty, 2) : 0;
        unset($data['line_total']);
        return $data;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_name')
                    ->label('المنتج / الوصف'),
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric(decimalPlaces: 2),
               
                TextColumn::make('line_total')
                    ->label('الإجمالي')
                    ->money('ILS'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(fn (array $data) => self::lineTotalToUnitPrice($data))
                    ->after(fn () => $this->getOwnerRecord()->recalculateTotal()),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(fn (array $data) => self::lineTotalToUnitPrice($data))
                    ->after(fn () => $this->getOwnerRecord()->recalculateTotal()),
                DeleteAction::make()
                    ->after(fn () => $this->getOwnerRecord()->recalculateTotal()),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(fn () => $this->getOwnerRecord()->recalculateTotal()),
                ]),
            ]);
    }
}
