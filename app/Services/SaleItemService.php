<?php

namespace App\Services;

use App\Models\Product;
use App\Models\productColor;
use App\Models\Sale_item;

use Illuminate\Support\Facades\DB;

class SaleItemService
{
    public function __construct(
        private readonly CurtainCostService $curtainCostService,
    ) {}

    public function onCreated(Sale_item $item): void
    {
        DB::transaction(function () use ($item) {
            $this->recalculateItem($item);
            $this->applyStockOnCreate($item);
            $this->updateSaleTotals($item);
        });
    }

    public function onUpdated(Sale_item $item): void
    {
        DB::transaction(function () use ($item) {
            $this->recalculateItem($item);
            $this->applyStockOnUpdate($item);
            $this->updateSaleTotals($item);
        });
    }

    public function onDeleted(Sale_item $item): void
    {
        DB::transaction(function () use ($item) {
            $this->applyStockOnDelete($item);
            $this->updateSaleTotals($item);
        });
    }

    private function recalculateItem(Sale_item $item): void
    {
        if ($item->item_type === 'ستارة') {
            $this->curtainCostService->recalculateSaleItem($item);
            return;
        }

        $product = $item->product;
        if (! $product) {
            return;
        }

        $qty        = (float) $item->quantity;
        $costPrice = (float) $product->cost_price;
        $sellTotal = (float) $item->sell_price;
        $extraCost = (float) $item->extra_cost;

        $item->total_cost = $costPrice * $qty;

        $item->profit = $sellTotal - ($item->total_cost + $extraCost);

        $item->net_profit = $item->profit;
        $item->saveQuietly();
    }

    private function applyStockOnCreate(Sale_item $item): void
    {
        if ($item->item_type === 'ستارة') {
            return;
        }

        $this->applyStockDelta(
            productId: (int) $item->product_id,
            productColorId: $item->product_color_id ? (int) $item->product_color_id : null,
            delta: -(float) $item->quantity,
        );

       
    }

    private function applyStockOnUpdate(Sale_item $item): void
    {
        if ($item->item_type === 'ستارة') {
            return;
        }

        $originalProductId = (int) $item->getOriginal('product_id');
        $originalProductColorId = $item->getOriginal('product_color_id');
        $originalProductColorId = $originalProductColorId ? (int) $originalProductColorId : null;
        $originalQty = (float) $item->getOriginal('quantity');

        $currentProductId = (int) $item->product_id;
        $currentProductColorId = $item->product_color_id ? (int) $item->product_color_id : null;
        $currentQty = (float) $item->quantity;

        $stockChanged =
            $originalProductId !== $currentProductId
            || $originalProductColorId !== $currentProductColorId
            || $originalQty !== $currentQty;

        if (! $stockChanged) {
            return;
        }

        // رجّع القديم للمخزون
        if ($originalProductId) {
            $this->applyStockDelta(
                productId: $originalProductId,
                productColorId: $originalProductColorId,
                delta: +$originalQty,
            );
        }

        // اخصم الجديد من المخزون
        $this->applyStockDelta(
            productId: $currentProductId,
            productColorId: $currentProductColorId,
            delta: -$currentQty,
        );

      
    }

    private function applyStockOnDelete(Sale_item $item): void
    {
        if ($item->item_type === 'ستارة') {
            return;
        }

        $this->applyStockDelta(
            productId: (int) $item->product_id,
            productColorId: $item->product_color_id ? (int) $item->product_color_id : null,
            delta: +(float) $item->quantity,
        );

        
    }

    private function applyStockDelta(int $productId, ?int $productColorId, float $delta): void
    {
        if ($productColorId) {
            /** @var productColor|null $color */
            $color = productColor::query()->lockForUpdate()->find($productColorId);
            if ($color) {
                // delta: + يرجع مخزون، - يخصم مخزون
                $color->stock = (float) $color->stock + $delta;
                $color->save();
            }
            return;
        }

        /** @var Product|null $product */
        $product = Product::query()->lockForUpdate()->find($productId);
        if (! $product) {
            return;
        }

        if ($delta >= 0) {
            $product->increment('stock', $delta);
        } else {
            $product->decrement('stock', abs($delta));
        }
    }

    private function updateSaleTotals(Sale_item $item): void
    {
        $sale = $item->sale;
        if (! $sale) {
            return;
        }

        $items = $sale->items()->get();

        $sale->total_price = $items->sum('sell_price');


        $sale->total_cost = $items->sum('total_cost') + $items->sum('extra_cost');
        $sale->profit = (float) $sale->total_price - (float) $sale->total_cost;
        $sale->saveQuietly();
    }
}
