<?php

namespace App\Observers;

use App\Models\Sale_item;
use App\Services\SaleItemService;

class SaleItemObserver
{
    public function created(Sale_item $item)
    {
        app(SaleItemService::class)->onCreated($item);
    }

    public function updated(Sale_item $item)
    {
        app(SaleItemService::class)->onUpdated($item);
    }

    public function deleted(Sale_item $item)
    {
        app(SaleItemService::class)->onDeleted($item);
    }
}
