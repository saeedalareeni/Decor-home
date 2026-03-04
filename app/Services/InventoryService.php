<?php

namespace App\Services;

use App\Models\InventoryBatch;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    /**
     * Create an inventory batch (e.g. when saving an invoice item).
     * Call inside a transaction if you are already in one.
     */
    public function addBatch(
        int $productId,
        ?int $productColorId,
        float $quantity,
        float $costPrice,
        ?int $invoiceItemId,
        \DateTimeInterface|string $receivedAt,
        ?string $notes = null
    ): InventoryBatch {
        return DB::transaction(function () use ($productId, $productColorId, $quantity, $costPrice, $invoiceItemId, $receivedAt, $notes) {
            return InventoryBatch::create([
                'product_id' => $productId,
                'product_color_id' => $productColorId,
                'invoice_item_id' => $invoiceItemId,
                'cost_price' => $costPrice,
                'quantity_in' => $quantity,
                'quantity_remaining' => $quantity,
                'received_at' => is_string($receivedAt) ? $receivedAt : $receivedAt->format('Y-m-d'),
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Consume stock from a batch (FIFO or specific batch).
     * Uses lockForUpdate() and must run inside a transaction.
     *
     * @return array{total_cost: float, inventory_batch_id: int}
     *
     * @throws InvalidArgumentException when stock is insufficient
     */
    public function consumeStock(
        int $productId,
        ?int $productColorId,
        float $quantityNeeded,
        ?int $selectedBatchId = null
    ): array {
        return DB::transaction(function () use ($productId, $productColorId, $quantityNeeded, $selectedBatchId) {
            $query = InventoryBatch::query()
                ->where('product_id', $productId)
                ->where('quantity_remaining', '>', 0)
                ->lockForUpdate();

            if ($productColorId !== null) {
                $query->where('product_color_id', $productColorId);
            } else {
                $query->whereNull('product_color_id');
            }

            if ($selectedBatchId !== null) {
                $query->where('id', $selectedBatchId);
            } else {
                $query->orderBy('received_at')->orderBy('id');
            }

            $batches = $query->get();
            $remaining = $quantityNeeded;
            $totalCost = 0.0;
            $usedBatchId = null;

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }
                $take = min($remaining, (float) $batch->quantity_remaining);
                if ($take <= 0) {
                    continue;
                }
                $batch->quantity_remaining = (float) $batch->quantity_remaining - $take;
                $batch->saveQuietly();
                $totalCost += $take * (float) $batch->cost_price;
                $remaining -= $take;
                $usedBatchId = (int) $batch->id;
            }

            if ($remaining > 0.0001) {
                throw new InvalidArgumentException(
                    'المخزون غير كافٍ. المطلوب: ' . $quantityNeeded . '، المتاح من الدفعات: ' . ($quantityNeeded - $remaining)
                );
            }

            return [
                'total_cost' => round($totalCost, 2),
                'inventory_batch_id' => $usedBatchId,
            ];
        });
    }

    /**
     * Return quantity to a batch (e.g. when a sale item is updated and quantity reduced).
     * Ensures quantity_remaining does not exceed the original quantity_in.
     * Call inside a transaction.
     *
     * @throws \InvalidArgumentException when returned quantity would exceed quantity_in
     */
    public function returnToBatch(int $batchId, float $quantity): void
    {
        DB::transaction(function () use ($batchId, $quantity) {
            $batch = InventoryBatch::where('id', $batchId)->lockForUpdate()->first();
            if (! $batch) {
                return;
            }
            $currentRemaining = (float) $batch->quantity_remaining;
            $originalIn = (float) $batch->quantity_in;
            $newRemaining = $currentRemaining + $quantity;

            if ($newRemaining > $originalIn) {
                throw new \InvalidArgumentException(
                    'الكمية المرجعة تتجاوز الكمية الأصلية للدفعة. المراد إرجاعه: ' . $quantity . '، المتاح للدفعة أصلاً: ' . $originalIn
                );
            }

            $batch->quantity_remaining = $newRemaining;
            $batch->saveQuietly();
        });
    }
}
