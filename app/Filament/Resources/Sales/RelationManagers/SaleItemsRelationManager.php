<?php

namespace App\Filament\Resources\Sales\RelationManagers;

use App\Models\Product;
use App\Models\productColor;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class SaleItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';


    public static function getPluralLabel(): ?string
    {
        return __("filament.sales");
    }

    public static function getModelLabel(): string
    {
        return __("filament.sales");
    }

    public function form(Schema $schema): Schema
    {

        $recalculate = function (callable $set, callable $get) {

            $quantity   = (float) $get('quantity');
            $unitPrice  = (float) $get('unit_price');

            $fabric = (float) $get('fabric_cost');
            $ring   = (float) $get('ring_cost');
            $tailor = (float) $get('tailor_cost');
            $extra  = (float) $get('extra_cost');

            $productCost = (float) optional(
                \App\Models\Product::find($get('product_id'))
            )->cost_price;

            $totalPrice = $quantity * $unitPrice;
            $extraCost  = $fabric + $ring + $tailor + $extra;

            $netProfit = $totalPrice - ($quantity * $productCost) - $extraCost;

            $set('total_price', round($totalPrice, 2));
            $set('net_profit', round($netProfit, 2));
        };


        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->label("المنتج")
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set, $get) use ($recalculate) {
                        $price = Product::find($state)?->selling_price ?? 0;
                        $set('unit_price', $price);
                        $recalculate($set, $get);
                    }),

                Select::make('product_color_id')
                    ->label("لون المنتج")
                    ->searchable()
                    ->options(
                        fn($get) =>
                        productColor::where('product_id', $get('product_id'))
                            ->pluck('color', 'id')
                    )
                    ->preload()
                    ->reactive()
                    ->required(),

                TextInput::make('quantity')
                    ->label("الكمية")

                    ->numeric()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(
                        fn($state, $set, $get)
                        => $recalculate($set, $get)
                    )->rule(function ($get) {
                        return function ($attribute, $value, $fail) use ($get) {
                            $color = ProductColor::find($get('product_color_id'));
                            if ($color && $value > $color->stock) {
                                $fail('الكمية المطلوبة أكبر من المخزون المتوفر');
                            }
                        };
                    }),

                TextInput::make("ring_cost")
                    ->label("تكلفة الحلق")
                    ->numeric()
                    ->prefix("ILS")
                    ->default(0)
                    ->reactive()
                    ->afterStateUpdated(
                        fn($state, $set, $get) => $recalculate($set, $get)
                    ),

                TextInput::make('tailor_cost')
                    ->label("تكلفة الخياطة")
                    ->numeric()
                    ->prefix("ILS")
                    ->default(0)
                    ->reactive()
                    ->afterStateUpdated(
                        fn($state, $set, $get)
                        => $recalculate($set, $get)
                    ),

                TextInput::make('extra_cost')
                    ->label("تكاليف اضافية")
                    ->numeric()
                    ->prefix("ILS")
                    ->default(0)
                    ->reactive()
                    ->afterStateUpdated(
                        fn($state, $set, $get)
                        => $recalculate($set, $get)
                    ),

                TextInput::make('unit_price')
                    ->label("سعر القطعه - ( متر )")
                    ->numeric()
                    ->prefix("ILS"),

                TextInput::make('total_price')
                    ->label("السعر الكلي")
                    ->numeric()
                    ->prefix("ILS")
                    ->readOnly(),

                TextInput::make('net_profit')
                    ->label("صافي الربح")
                    ->numeric()
                    ->prefix("ILS")
                    ->readOnly(),
            ]);
    }

    // public function infolist(Schema $schema): Schema
    // {
    //     return $schema
    //         ->components([
    //             TextEntry::make('items')
    //                 ->label('Total')
    //                 ->content(fn($record) => $record->items->count()),
    //         ]);
    // }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('items')
            ->columns([
                TextColumn::make('product.name')->label('المنتج'),
                TextColumn::make('quantity')->label('الكمية'),
                TextColumn::make('unit_price')->label('سعر القطعة')->money('ILS', locale: "en"),
                TextColumn::make('total_price')->label('السعر الكلي')->money('ILS', locale: "en")
                    ->summarize(
                        Sum::make()
                            ->label('إجمالي السعر الكلي')
                            ->money('ILS', locale: "en")
                    ),
                TextColumn::make('net_profit')->label('صافي الربح')->money('ILS', locale: "en")
                    ->summarize(
                        Sum::make()
                            ->label('إجمالي صافي الربح')
                            ->money('ILS', locale: "en")
                    ),
            ])
            ->filters([
                Filter::make('this_month')
                    ->label('هذا الشهر')
                    ->query(
                        fn($query) =>
                        $query->whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year)
                    ),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                // DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
