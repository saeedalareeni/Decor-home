<?php

namespace App\Services;

use App\Models\Sale_item;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SaleItemService
{
    public function __construct(
        private readonly CurtainCostService $curtainCostService,
        private readonly InventoryService $inventoryService,
    ) {}

    public function onCreated(Sale_item $item): void
    {
        DB::transaction(function () use ($item) {
            $this->consumeBatchForItem($item);
            $this->recalculateItem($item);
            $this->applyStockOnCreate($item);
            $this->updateSaleTotals($item);
        });
    }

    public function onUpdated(Sale_item $item): void
    {
        DB::transaction(function () use ($item) {
            $this->adjustBatchOnUpdate($item);
            $this->consumeBatchForItem($item);
            $this->recalculateItem($item);
            $this->applyStockOnUpdate($item);
            $this->updateSaleTotals($item);
        });
    }

    public function onDeleted(Sale_item $item): void
    {
        DB::transaction(function () use ($item) {
            $this->returnBatchOnDelete($item);
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

        $sellTotal = (float) $item->sell_price;
        $extraCost = (float) $item->extra_cost;
        // total_cost already set by consumeBatchForItem for منتج عادي when using batches
        $totalCost = (float) $item->total_cost;
        $item->profit = $sellTotal - ($totalCost + $extraCost);
        $item->net_profit = $item->profit;
        $item->saveQuietly();
    }

    /** Consume from inventory batch for regular product; sets total_cost and inventory_batch_id. */
    private function consumeBatchForItem(Sale_item $item): void
    {
        if ($item->item_type !== 'منتج عادي' || ! $item->product_id) {
            return;
        }
        try {
            $result = $this->inventoryService->consumeStock(
                (int) $item->product_id,
                $item->product_color_id ? (int) $item->product_color_id : null,
                (float) $item->quantity,
                $item->inventory_batch_id ? (int) $item->inventory_batch_id : null
            );
            $item->total_cost = $result['total_cost'];
            $item->inventory_batch_id = $result['inventory_batch_id'];
            $item->saveQuietly();
        } catch (InvalidArgumentException $e) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quantity' => [$e->getMessage()],
            ]);
        }
    }

    /** On update: return previously consumed quantity to batch before re-consuming. */
    private function adjustBatchOnUpdate(Sale_item $item): void
    {
        if ($item->item_type !== 'منتج عادي' || ! $item->product_id) {
            return;
        }
        $oldBatchId = $item->getOriginal('inventory_batch_id');
        $oldQty = (float) $item->getOriginal('quantity');
        if ($oldBatchId && $oldQty > 0) {
            try {
                $this->inventoryService->returnToBatch((int) $oldBatchId, $oldQty);
            } catch (InvalidArgumentException $e) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'quantity' => [$e->getMessage()],
                ]);
            }
        }
    }

    /** On delete: return consumed quantity to batch. */
    private function returnBatchOnDelete(Sale_item $item): void
    {
        if ($item->item_type !== 'منتج عادي' || ! $item->inventory_batch_id) {
            return;
        }
        try {
            $this->inventoryService->returnToBatch((int) $item->inventory_batch_id, (float) $item->quantity);
        } catch (InvalidArgumentException $e) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quantity' => [$e->getMessage()],
            ]);
        }
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


        $sale->total_cost = $items->sum(function (Sale_item $it) {
            if ($it->item_type === 'ستارة') {
                return (float) $it->total_cost + (float) ($it->extra_cost ?? 0);
            }
            return (float) $it->total_cost + (float) ($it->extra_cost ?? 0);
        });
        $sale->profit = (float) $sale->total_price - (float) $sale->total_cost;
        $sale->saveQuietly();
    }
}
