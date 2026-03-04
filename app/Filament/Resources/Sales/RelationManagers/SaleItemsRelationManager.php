<?php

namespace App\Filament\Resources\Sales\RelationManagers;

use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\productColor;
use Filament\Actions\AssociateAction;
use Filament\Notifications\Notification;
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
use Filament\Forms\Components\Textarea;
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
use Illuminate\Validation\ValidationException;

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
                    ->options(
                        Product::all()
                            ->mapWithKeys(fn(Product $p) => [
                                $p->id => $p->name . ' (مخزون: ' . ($p->stock ?? 0) . ' | سعر الجمله: ' . ($p->cost_price ?? 0) . ')',
                            ])
                            ->all()
                    )
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($set) {
                        $set('product_color_id', null);
                        $set('inventory_batch_id', null);
                    })
                    ->visible(fn($get) => $get('item_type') === 'منتج عادي'),

                Select::make('product_color_id')
                    ->label('لون المنتج')
                    ->searchable()
                    ->preload()
                    ->options(function ($get) {
                        $productId = $get('product_id');
                        if (!$productId) {
                            return [];
                        }
                        return productColor::where('product_id', $productId)
                            ->get()
                            ->mapWithKeys(fn(productColor $pc) => [
                                $pc->id => $pc->color . ' (مخزون: ' . ($pc->stock ?? 0) . ')',
                            ])
                            ->all();
                    })
                    ->visible(
                        fn($get) =>
                        $get('product_id') &&
                            productColor::where('product_id', $get('product_id'))->exists()
                            && $get('item_type') === 'منتج عادي'
                    )
                    ->required(fn($get) => $get('product_id') && productColor::where('product_id', $get('product_id'))->exists())
                    ->reactive()
                    ->afterStateUpdated(fn ($set) => $set('inventory_batch_id', null)),

                Select::make('inventory_batch_id')
                    ->label('دفعة الجملة / سعر الشراء')
                    ->options(function ($get) {
                        $productId = $get('product_id');
                        $colorId = $get('product_color_id');
                        if (!$productId) {
                            return [];
                        }
                        $query = InventoryBatch::query()
                            ->where('product_id', $productId)
                            ->where('quantity_remaining', '>', 0);
                        if ($colorId) {
                            $query->where('product_color_id', $colorId);
                        } else {
                            $query->whereNull('product_color_id');
                        }
                        return $query->orderBy('received_at')->orderBy('id')
                            ->get()
                            ->mapWithKeys(fn (InventoryBatch $b) => [
                                $b->id => $b->batch_label,
                            ])
                            ->all();
                    })
                    ->searchable()
                    ->placeholder('أول وارد أول صادر (FIFO)')
                    ->visible(fn ($get) => $get('item_type') === 'منتج عادي' && $get('product_id'))
                    ->reactive(),

                TextInput::make('quantity')
                    ->label('الكمية')
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
                    ->required(),

                Textarea::make('notes')
                    ->label('ملاحظات'),

                // هنا يظهر تفاصيل الستارة (CurtainCosts)
                Repeater::make('curtainCosts')
                    ->relationship('curtainCosts')
                    ->label('تفاصيل الستارة (حديد/شيفون/حلق...)')
                    ->visible(fn($get) => $get('item_type') === 'ستارة')
                    ->schema([
                        Select::make('product_id')
                            ->label('المكون')
                            ->options(
                                Product::whereIn('type', ['ستائر', 'شيفون', 'بطانة', 'حلق', 'حديد', 'مفروشات'])
                                    ->get()
                                    ->mapWithKeys(fn(Product $p) => [
                                        $p->id => $p->name . ' (مخزون: ' . ($p->stock ?? 0) . ' | سعر الجمله: ' . ($p->cost_price ?? 0) . ')',
                                    ])
                                    ->all()
                            )
                            ->reactive()
                            ->afterStateUpdated(fn($set) => $set('product_color_id', null))
                            ->searchable()
                            ->required(),

                        Select::make('product_color_id')
                            ->label('لون المكون')
                            ->options(function ($get) {
                                $productId = $get('product_id');
                                if (!$productId) {
                                    return [];
                                }
                                return productColor::where('product_id', $productId)
                                    ->get()
                                    ->mapWithKeys(fn(productColor $pc) => [
                                        $pc->id => $pc->color . ' (مخزون: ' . ($pc->stock ?? 0) . ')',
                                    ])
                                    ->all();
                            })
                            ->visible(fn($get) => $get('product_id') && productColor::where('product_id', $get('product_id'))->exists())
                            ->required(fn($get) => $get('product_id') && productColor::where('product_id', $get('product_id'))->exists())
                            ->searchable()
                            ->reactive(),

                        TextInput::make('quantity')
                            ->label('الكمية')
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
                TextColumn::make('product.name')->label('المنتج')
                    ->placeholder('—'),
                TextColumn::make('color_display')->label('اللون')
                    ->getStateUsing(function ($record) {
                        if ($record->item_type === 'منتج عادي') {
                            return $record->colors?->color ?? '—';
                        }
                        if ($record->item_type === 'ستارة') {
                            $parts = $record->curtainCosts->map(function ($c) {
                                $colorName = $c->productColor?->color ?? '—';
                                return $c->product?->name . ': ' . $colorName . ' (×' . $c->quantity . ')';
                            });
                            return $parts->isEmpty() ? '—' : $parts->implode(' | ');
                        }
                        return '—';
                    }),
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
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $this->validateSaleItemStock($data, null);
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateDataUsing(function (array $data, \Illuminate\Database\Eloquent\Model $record): array {
                        $this->validateSaleItemStock($data, $record);
                        return $data;
                    }),
                // DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private function validateSaleItemStock(array $data, $record): void
    {
        if (($data['item_type'] ?? '') === 'منتج عادي' && ! empty($data['product_id'])) {
            $productId = (int) $data['product_id'];
            $colorId = isset($data['product_color_id']) ? (int) $data['product_color_id'] : null;
            $available = $this->getAvailableStock($productId, $colorId);
            if ($record && $record->product_id == $productId && $record->product_color_id == $colorId) {
                $available += (float) $record->quantity;
            }
            $needed = (float) ($data['quantity'] ?? 0);
            if ($available < $needed) {
                $msg = "المخزون غير كافٍ. المتاح: " . round($available, 2) . "، المطلوب: {$needed}";
                Notification::make()->danger()->title($msg)->persistent()->send();
                throw ValidationException::withMessages(['quantity' => [$msg]]);
            }
        }

        if (($data['item_type'] ?? '') === 'ستارة' && ! empty($data['curtainCosts'])) {
            $existingIds = $record?->curtainCosts?->keyBy('id') ?? collect();
            foreach ($data['curtainCosts'] as $i => $cost) {
                $productId = (int) ($cost['product_id'] ?? 0);
                $colorId = isset($cost['product_color_id']) ? (int) $cost['product_color_id'] : null;
                $qty = (float) ($cost['quantity'] ?? 0);
                $available = $this->getAvailableStock($productId, $colorId);
                $existing = $cost['id'] ?? null;
                if ($existing && $existingCost = $existingIds->get($existing)) {
                    if ($existingCost->product_id == $productId && $existingCost->product_color_id == $colorId) {
                        $available += (float) $existingCost->quantity;
                    }
                }
                if ($available < $qty) {
                    $productName = Product::find($productId)?->name ?? 'المنتج';
                    $msg = "المخزون غير كافٍ للمكوّن ({$productName}). المتاح: " . round($available, 2) . "، المطلوب: {$qty}";
                    Notification::make()->danger()->title($msg)->persistent()->send();
                    throw ValidationException::withMessages(["curtainCosts.{$i}.quantity" => [$msg]]);
                }
            }
        }
    }

    private function getAvailableStock(int $productId, ?int $productColorId): float
    {
        if (! $productId) {
            return 0;
        }
        $batchTotal = InventoryBatch::query()
            ->where('product_id', $productId)
            ->where('quantity_remaining', '>', 0);
        if ($productColorId) {
            $batchTotal->where('product_color_id', $productColorId);
        } else {
            $batchTotal->whereNull('product_color_id');
        }
        $fromBatches = (float) $batchTotal->sum('quantity_remaining');
        if ($fromBatches > 0) {
            return $fromBatches;
        }
        if ($productColorId) {
            $color = productColor::find($productColorId);
            return (float) ($color->stock ?? 0);
        }
        $product = Product::find($productId);
        return (float) ($product->stock ?? 0);
    }
}
