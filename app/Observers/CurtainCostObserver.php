<?php

namespace App\Observers;

use App\Models\curtainCost;
use App\Services\CurtainCostService;

class CurtainCostObserver
{

    public function created(curtainCost $cost)
    {
        app(CurtainCostService::class)->onCreated($cost);
    }

    public function updated(curtainCost $cost)
    {
        app(CurtainCostService::class)->onUpdated($cost);
    }

    public function deleted(curtainCost $cost)
    {
        app(CurtainCostService::class)->onDeleted($cost);
    }
}
