<?php

namespace App\Observers;

use App\Models\Purchase;
use App\Models\Purchase_item;
use App\Models\Stock_movement;
use Illuminate\Support\Facades\DB;

class Purchase_itemObserver
{
    /**
     * Handle the Purchase_item "created" event.
     */
    public function created(Purchase_item $purchase_item): void
    {
        $product = $purchase_item->product;
        $product->increment('stock', $purchase_item->quantity);

        $product->update([
            'cost_price' => $purchase_item->cost_price,
        ]);

        Stock_movement::create([
            'product_id'  => $purchase_item->product_id,
            'type'         => 'in',
            'quantity'     => $purchase_item->quantity,
            'reference'    => 'purchase',
            'reference_id' => $purchase_item->purchase_id,
        ]);

        $this->recalculatePurchaseTotal($purchase_item->purchase);
    }

    /**
     * Handle the Purchase_item "updated" event.
     */
    public function updated(Purchase_item $purchase_item): void
    {
        $oldQty = $purchase_item->getOriginal('quantity');
        $diff = $purchase_item->quantity - $oldQty;

        if ($diff != 0) {
            $purchase_item->product->increment('stock', $diff);

            Stock_movement::create([
                'product_id'  => $purchase_item->product_id,
                'type'         => $diff > 0 ? 'in' : 'out',
                'quantity'     => abs($diff),
                'reference'    => 'purchase',
                'reference_id' => $purchase_item->purchase_id,
            ]);
        }

        $this->recalculatePurchaseTotal($purchase_item->purchase);
    }

    /**
     * Handle the Purchase_item "deleted" event.
     */
    public function deleted(Purchase_item $purchase_item): void
    {
        $purchase_item->product->decrement('stock', $purchase_item->quantity);

        Stock_movement::create([
            'product_id'  => $purchase_item->product_id,
            'type'         => 'out',
            'quantity'     => $purchase_item->quantity,
            'reference'    => 'purchase',
            'reference_id' => $purchase_item->purchase_id,
        ]);

        $this->recalculatePurchaseTotal($purchase_item->purchase);
    }

    private function recalculatePurchaseTotal(Purchase $purchase): void
    {
        $total = $purchase->items()
            ->sum(DB::raw('quantity * cost_price'));

        $purchase->update([
            'total_amount' => $total,
        ]);
    }

    /**
     * Handle the Purchase_item "restored" event.
     */
    public function restored(Purchase_item $purchase_item): void
    {
        //
    }

    /**
     * Handle the Purchase_item "force deleted" event.
     */
    public function forceDeleted(Purchase_item $purchase_item): void
    {
        //
    }
}
