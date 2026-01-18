<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\Sale_item;
use App\Models\Stock_movement;
use Illuminate\Support\Facades\DB;

class Sale_itemObserver
{

    public function saving(Sale_item $item): void
    {
        $item->loadMissing('product');

        $quantity = (float) $item->quantity;

        $item->total_price = $quantity * (float) $item->unit_price;

        $extraCost =
            (float) $item->fabric_cost +
            (float) $item->ring_cost +
            (float) $item->tailor_cost +
            (float) $item->extra_cost;

        $productCost = $quantity * (float) $item->product->cost_price;

        $item->total_cost = $extraCost + $productCost;

        $item->net_profit = $item->total_price - $item->total_cost;
    }

    /**
     * Handle the Sale_item "created" event.
     */
    public function created(Sale_item $sale_item): void
    {
        DB::transaction(function () use ($sale_item) {

            $product = Product::lockForUpdate()->findOrFail($sale_item->product_id);

            if ($product->stock < $sale_item->quantity) {
                throw new \Exception('المخزون غير كافٍ');
            }

            Stock_movement::create([
                'product_id'   => $sale_item->product_id,
                'type'         => 'out',
                'quantity'     => $sale_item->quantity,
                'reference'    => 'sale',
                'reference_id' => $sale_item->sale_id,
            ]);

            $product->decrement('stock', $sale_item->quantity);
        });
    }

    /**
     * Handle the Sale_item "updated" event.
     */
    public function updated(Sale_item $sale_item): void
    {
        if ($sale_item->wasChanged('quantity')) {

            $old = $sale_item->getOriginal('quantity');
            $new = $sale_item->quantity;
            $diff = $new - $old;

            if ($diff === 0) {
                return;
            }

            DB::transaction(function () use ($sale_item, $diff) {

                $product = Product::lockForUpdate()->findOrFail($sale_item->product_id);

                if ($diff > 0 && $product->stock < $diff) {
                    throw new \Exception('المخزون غير كافٍ');
                }

                Stock_movement::create([
                    'product_id'   => $sale_item->product_id,
                    'type'         => $diff > 0 ? 'out' : 'in',
                    'quantity'     => abs($diff),
                    'reference'    => 'adjustment',
                    'reference_id' => $sale_item->sale_id,
                ]);

                if ($diff > 0) {
                    $product->decrement('stock', $diff);
                } else {
                    $product->increment('stock', abs($diff));
                }
            });
        }
    }

    /**
     * Handle the Sale_item "deleted" event.
     */
    public function deleted(Sale_item $sale_item): void
    {
        DB::transaction(function () use ($sale_item) {

            $product = Product::lockForUpdate()->findOrFail($sale_item->product_id);

            Stock_movement::create([
                'product_id'   => $sale_item->product_id,
                'type'         => 'in',
                'quantity'     => $sale_item->quantity,
                'reference'    => 'adjustment',
                'reference_id' => $sale_item->sale_id,
            ]);

            $product->increment('stock', $sale_item->quantity);
        });
    }

    /**
     * Handle the Sale_item "restored" event.
     */
    public function restored(Sale_item $sale_item): void
    {
        //
    }

    /**
     * Handle the Sale_item "force deleted" event.
     */
    public function forceDeleted(Sale_item $sale_item): void
    {
        //
    }
}
