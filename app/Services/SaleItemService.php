<?php

namespace App\Services;

use App\Models\Sale_item;
use App\Models\StockTransaction;

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
        if ($item->item_type === 'ستارة' || ! $item->product_id) {
            return;
        }

        $this->createStockTransactionForSaleItem($item);
    }

    private function applyStockOnUpdate(Sale_item $item): void
    {
        if ($item->item_type === 'ستارة') {
            return;
        }

        // دائماً نزامن حركة المخزون مع حالة البند الحالية (حذف القديمة ثم إنشاء جديدة)
        // حتى لو واجهنا مشكلة في getOriginal() عند التعديل من Filament أو غيره
        $item->stockTransaction?->delete();

        if ($item->product_id) {
            $this->createStockTransactionForSaleItem($item);
        }
    }

    private function applyStockOnDelete(Sale_item $item): void
    {
        if ($item->item_type === 'ستارة') {
            return;
        }

        $item->stockTransaction?->delete();
    }

    /** إنشاء حركة مخزون إخراج مرتبطة ببند البيع (وتحديث المخزون يتم تلقائياً من نموذج StockTransaction) */
    private function createStockTransactionForSaleItem(Sale_item $item): void
    {
        StockTransaction::create([
            'product_id' => (int) $item->product_id,
            'product_color_id' => $item->product_color_id ? (int) $item->product_color_id : null,
            'quantity' => (float) $item->quantity,
            'type' => StockTransaction::TYPE_OUT,
            'reference_type' => Sale_item::class,
            'reference_id' => $item->id,
        ]);
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
