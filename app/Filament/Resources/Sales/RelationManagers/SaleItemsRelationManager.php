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
use Filament\Forms\Components\Repeater;
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

        return $schema
            ->components([
                Select::make('item_type')
                    ->label('نوع العنصر')
                    ->options([
                        'ستارة' => 'ستارة',
                        'منتج عادي' => 'منتج عادي',
                    ])->required()
                    ->reactive()
                    ->afterStateUpdated(function ($set, $state) {
                        if ($state === 'ستارة') {
                            $set('product_id', null);
                            $set('product_color_id', null);
                            $set('quantity', 0);
                        }
                    }),

                Select::make('product_id')
                    ->label('المنتج')
                    ->options(Product::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn($set) => $set('product_color_id', null))
                    ->visible(fn($get) => $get('item_type') === 'منتج عادي'),

                Select::make('product_color_id')
                    ->label('لون المنتج')
                    ->searchable()
                    ->preload()
                    ->options(
                        fn($get) =>
                        $get('product_id')
                            ? \App\Models\ProductColor::where('product_id', $get('product_id'))
                            ->pluck('color', 'id')
                            : []
                    )
                    ->visible(
                        fn($get) =>
                        $get('product_id') &&
                            \App\Models\ProductColor::where('product_id', $get('product_id'))->exists()
                            && $get('item_type') === 'منتج عادي'
                    )
                    ->reactive(),

                TextInput::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->default(1)
                    ->visible(fn($get) => $get('item_type') === 'منتج عادي')
                    ->required(),

                TextInput::make('sell_price')
                    ->label('سعر البيع للزبون')
                    ->numeric()->required(),


                TextInput::make('sewing_cost')
                    ->label('تكلفة الخياطة')
                    ->numeric()
                    ->default(0)
                    ->visible(fn($get) => $get('item_type') === 'ستارة')->required(),

                TextInput::make('extra_cost')
                    ->label('تكاليف إضافية')
                    ->numeric()
                    ->default(0)
                    ->visible(fn($get) => $get('item_type') === 'ستارة')->required(),

                // هنا يظهر تفاصيل الستارة (CurtainCosts)
                Repeater::make('curtainCosts')
                    ->relationship('curtainCosts')
                    ->label('تفاصيل الستارة (حديد/شيفون/حلق...)')
                    ->visible(fn($get) => $get('item_type') === 'ستارة')
                    ->schema([
                        Select::make('product_id')
                            ->label('المكون')
                            ->options(
                                Product::whereIn('type', ['ستائر', 'شيفون', 'بطانة', 'حلق', 'حديد'])
                                    ->pluck('name', 'id')
                            )
                            ->reactive()
                            ->afterStateUpdated(fn($set) => $set('product_color_id', null))
                            ->required(),

                        Select::make('product_color_id')
                            ->label('اللون')
                            ->options(
                                fn($get) =>
                                $get('product_id')
                                    ? \App\Models\ProductColor::where('product_id', $get('product_id'))
                                    ->pluck('color', 'id')
                                    : []
                            )
                            ->reactive()
                            ->required(),

                        TextInput::make('quantity')
                            ->label('الكمية')
                            ->numeric()
                            ->required(),
                    ]),

                TextInput::make('total_cost')
                    ->label('التكلفة')
                    ->disabled(),

                TextInput::make('profit')
                    ->label('الربح')
                    ->disabled(),
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
                TextColumn::make('id')->label('ID'),
                TextColumn::make('item_type')->label('نوع العنصر')->badge(),
                TextColumn::make('product.name')->label('المنتج'),
                TextColumn::make('quantity')->label('الكمية'),
                TextColumn::make('sell_price')->label('سعر البيع'),
                TextColumn::make('total_cost')->label('التكلفة'),
                TextColumn::make('profit')->label('الربح'),

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
