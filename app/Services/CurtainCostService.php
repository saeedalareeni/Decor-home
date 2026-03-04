<?php

namespace App\Services;

use App\Models\curtainCost;
use App\Models\Sale_item;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CurtainCostService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
    ) {}

    public function onCreated(curtainCost $cost): void
    {
        DB::transaction(function () use ($cost) {
            $this->consumeBatchForCurtainCost($cost);
            $this->applyStockOnCreate($cost);
            $this->recalculateSaleItem($cost->sale_item);
            $this->updateSaleTotals($cost->sale_item);
        });
    }

    public function onUpdated(curtainCost $cost): void
    {
        DB::transaction(function () use ($cost) {
            $this->returnBatchForCurtainCostIfNeeded($cost);
            $this->consumeBatchForCurtainCost($cost);
            $this->applyStockOnUpdate($cost);
            $this->recalculateSaleItem($cost->sale_item);
            $this->updateSaleTotals($cost->sale_item);
        });
    }

    public function onDeleted(curtainCost $cost): void
    {
        DB::transaction(function () use ($cost) {
            $this->returnBatchOnCurtainCostDelete($cost);
            $this->applyStockOnDelete($cost);
            $this->recalculateSaleItem($cost->sale_item);
            $this->updateSaleTotals($cost->sale_item);
        });
    }

    /** استهلاك من الدفعة لكل مكوّن ستارة (FIFO) وحفظ الدفعة والتكلفة على الـ cost */
    private function consumeBatchForCurtainCost(curtainCost $cost): void
    {
        try {
            $result = $this->inventoryService->consumeStock(
                (int) $cost->product_id,
                $cost->product_color_id ? (int) $cost->product_color_id : null,
                (float) $cost->quantity,
                null
            );
            $cost->inventory_batch_id = $result['inventory_batch_id'];
            $cost->consumed_cost = $result['total_cost'];
            $cost->saveQuietly();
        } catch (InvalidArgumentException $e) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quantity' => [$e->getMessage()],
            ]);
        }
    }

    /** عند التعديل: إرجاع الكمية القديمة للدفعة قبل استهلاك الجديدة */
    private function returnBatchForCurtainCostIfNeeded(curtainCost $cost): void
    {
        $oldBatchId = $cost->getOriginal('inventory_batch_id');
        $oldQty = (float) $cost->getOriginal('quantity');
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

    /** عند الحذف: إرجاع الكمية للدفعة */
    private function returnBatchOnCurtainCostDelete(curtainCost $cost): void
    {
        if (! $cost->inventory_batch_id) {
            return;
        }
        try {
            $this->inventoryService->returnToBatch((int) $cost->inventory_batch_id, (float) $cost->quantity);
        } catch (InvalidArgumentException $e) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quantity' => [$e->getMessage()],
            ]);
        }
    }

    public function recalculateSaleItem(?Sale_item $saleItem): void
    {
        if (! $saleItem || $saleItem->item_type !== 'ستارة') {
            return;
        }

        $saleItem->loadMissing(['curtainCosts.product']);

        $componentsCost = $saleItem->curtainCosts->sum(function (curtainCost $cost) {
            if ($cost->consumed_cost !== null && $cost->consumed_cost > 0) {
                return (float) $cost->consumed_cost;
            }
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
        $this->createStockTransactionForCurtainCost($cost);
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

        $cost->stockTransaction?->delete();

        $this->createStockTransactionForCurtainCost($cost);
    }

    private function applyStockOnDelete(curtainCost $cost): void
    {
        $cost->stockTransaction?->delete();
    }

    private function createStockTransactionForCurtainCost(curtainCost $cost): void
    {
        StockTransaction::create([
            'product_id' => (int) $cost->product_id,
            'product_color_id' => $cost->product_color_id ? (int) $cost->product_color_id : null,
            'quantity' => (float) $cost->quantity,
            'type' => StockTransaction::TYPE_OUT,
            'reference_type' => curtainCost::class,
            'reference_id' => $cost->id,
        ]);
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

        $sale->total_cost = $items->sum(function (Sale_item $it) {
            if ($it->item_type === 'ستارة') {
                return (float) $it->total_cost;
            }
            return (float) $it->total_cost + (float) ($it->extra_cost ?? 0);
        });
        $sale->profit = (float) $sale->total_price - (float) $sale->total_cost;
        $sale->saveQuietly();
    }
}
