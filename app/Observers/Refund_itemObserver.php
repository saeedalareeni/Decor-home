<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\Refund_item;
use App\Models\Stock_movement;
use Illuminate\Support\Facades\DB;

class Refund_itemObserver
{
    /**
     * Handle the Refund_item "created" event.
     */
    public function created(Refund_item $refund_item): void
    {
        DB::transaction(function () use ($refund_item) {

            $product = Product::lockForUpdate()->findOrFail($refund_item->product_id);

            Stock_movement::create([
                'product_id'   => $refund_item->product_id,
                'type'         => 'in',
                'quantity'     => $refund_item->quantity,
                'reference'    => 'refund',
                'reference_id' => $refund_item->refund_id,
            ]);

            $product->increment('stock', $refund_item->quantity);
        });
    }

    /**
     * Handle the Refund_item "updated" event.
     */
    public function updated(Refund_item $refund_item): void
    {
        if ($refund_item->wasChanged('quantity')) {

            $old = $refund_item->getOriginal('quantity');
            $new = $refund_item->quantity;
            $diff = $new - $old;

            if ($diff === 0) {
                return;
            }

            DB::transaction(function () use ($refund_item, $diff) {

                $product = Product::lockForUpdate()->findOrFail($refund_item->product_id);

                if ($diff > 0 && $product->stock < $diff) {
                    throw new \Exception('المخزون غير كافٍ');
                }

                Stock_movement::create([
                    'product_id'   => $refund_item->product_id,
                    'type'         => $diff > 0 ? 'in' : 'out',
                    'quantity'     => abs($diff),
                    'reference'    => 'adjustment',
                    'reference_id' => $refund_item->refund_id,
                ]);

                if ($diff > 0) {
                    $product->increment('stock', $diff); // كمية زائدة → زيادة المخزون
                } else {
                    $product->decrement('stock', abs($diff)); // كمية أقل → خصم المخزون
                }
            });
        }
    }

    /**
     * Handle the Refund_item "deleted" event.
     */
    public function deleted(Refund_item $refund_item): void
    {
        DB::transaction(function () use ($refund_item) {

            $product = Product::lockForUpdate()->findOrFail($refund_item->product_id);

            Stock_movement::create([
                'product_id'   => $refund_item->product_id,
                'type'         => 'out',
                'quantity'     => $refund_item->quantity,
                'reference'    => 'adjustment',
                'reference_id' => $refund_item->refund_id,
            ]);

            $product->decrement('stock', $refund_item->quantity);
        });
    }

    /**
     * Handle the Refund_item "restored" event.
     */
    public function restored(Refund_item $refund_item): void
    {
        //
    }

    /**
     * Handle the Refund_item "force deleted" event.
     */
    public function forceDeleted(Refund_item $refund_item): void
    {
        //
    }
}
