<?php

namespace App\Services;

use App\Models\curtainCost;
use App\Models\Product;
use App\Models\productColor;
use App\Models\Sale_item;
use Illuminate\Support\Facades\DB;

class CurtainCostService
{
    public function onCreated(curtainCost $cost): void
    {
        DB::transaction(function () use ($cost) {
            $this->applyStockOnCreate($cost);
            $this->recalculateSaleItem($cost->sale_item);
            $this->updateSaleTotals($cost->sale_item);
        });
    }

    public function onUpdated(curtainCost $cost): void
    {
        DB::transaction(function () use ($cost) {
            $this->applyStockOnUpdate($cost);
            $this->recalculateSaleItem($cost->sale_item);
            $this->updateSaleTotals($cost->sale_item);
        });
    }

    public function onDeleted(curtainCost $cost): void
    {
        DB::transaction(function () use ($cost) {
            $this->applyStockOnDelete($cost);
            $this->recalculateSaleItem($cost->sale_item);
            $this->updateSaleTotals($cost->sale_item);
        });
    }

    public function recalculateSaleItem(?Sale_item $saleItem): void
    {
        if (! $saleItem || $saleItem->item_type !== 'ستارة') {
            return;
        }

        $saleItem->loadMissing(['curtainCosts.product']);

        $componentsCost = $saleItem->curtainCosts->sum(function (curtainCost $cost) {
            $product = $cost->product;
            if (! $product) {
                return 0;
            }

            return (float) $cost->quantity * (float) $product->cost_price;
        });

        $totalCost =
            (float) $componentsCost +
            (float) ($saleItem->sewing_cost ?? 0) +
            (float) ($saleItem->extra_cost ?? 0);

        $saleItem->total_cost = $totalCost;
        $saleItem->profit = (float) ($saleItem->sell_price ?? 0) - $totalCost;
        $saleItem->net_profit = $saleItem->profit;
        $saleItem->saveQuietly();
    }

    private function applyStockOnCreate(curtainCost $cost): void
    {
        $this->applyStockDelta(
            productId: (int) $cost->product_id,
            productColorId: $cost->product_color_id ? (int) $cost->product_color_id : null,
            delta: -(float) $cost->quantity,
        );

       
    }

    private function applyStockOnUpdate(curtainCost $cost): void
    {
        $originalProductId = (int) $cost->getOriginal('product_id');
        $originalProductColorId = $cost->getOriginal('product_color_id');
        $originalProductColorId = $originalProductColorId ? (int) $originalProductColorId : null;
        $originalQty = (float) $cost->getOriginal('quantity');

        $currentProductId = (int) $cost->product_id;
        $currentProductColorId = $cost->product_color_id ? (int) $cost->product_color_id : null;
        $currentQty = (float) $cost->quantity;

        $stockChanged =
            $originalProductId !== $currentProductId
            || $originalProductColorId !== $currentProductColorId
            || $originalQty !== $currentQty;

        if (! $stockChanged) {
            return;
        }

        // رجّع القديم
        if ($originalProductId) {
            $this->applyStockDelta(
                productId: $originalProductId,
                productColorId: $originalProductColorId,
                delta: +$originalQty,
            );
        }

        // اخصم الجديد
        $this->applyStockDelta(
            productId: $currentProductId,
            productColorId: $currentProductColorId,
            delta: -$currentQty,
        );

       
    }

    private function applyStockOnDelete(curtainCost $cost): void
    {
        $this->applyStockDelta(
            productId: (int) $cost->product_id,
            productColorId: $cost->product_color_id ? (int) $cost->product_color_id : null,
            delta: +(float) $cost->quantity,
        );

       
    }

    private function applyStockDelta(int $productId, ?int $productColorId, float $delta): void
    {
        if ($productColorId) {
            $color = productColor::query()->lockForUpdate()->find($productColorId);
            if ($color) {
                $color->stock = (float) $color->stock + $delta;
                $color->save();
            }
            return;
        }

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

    private function updateSaleTotals(?Sale_item $item): void
    {
        $sale = $item?->sale;
        if (! $sale) {
            return;
        }

        $items = $sale->items()->get();

        $sale->total_price = $items->sum(function (Sale_item $it) {
            return $it->item_type === 'ستارة'
                ? (float) $it->sell_price
                : (float) $it->sell_price * (float) $it->quantity;
        });

        $sale->total_cost = $items->sum('total_cost');
        $sale->profit = (float) $sale->total_price - (float) $sale->total_cost;
        $sale->saveQuietly();
    }
}
