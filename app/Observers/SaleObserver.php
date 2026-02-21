<?php

namespace App\Observers;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class SaleObserver
{
    /**
     * عند حذف مبيعة (سوفت أو فورس): حذف حركات المخزون لأصناف البيع وترجيع الكميات للمخزون.
     */
    public function deleting(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $sale->items()->with('stockTransaction')->each(function ($item) {
                $item->stockTransaction?->delete();
            });
        });
    }
}
