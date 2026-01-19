<?php

namespace App\Observers;

use App\Models\curtainCost;
use App\Models\Sale_item;
use App\Models\StockTransaction;

class CurtainCostObserver
{
    /**
     * عند إنشاء CurtainCost جديد - خصم الكمية من المخزون
     */
    public function created(curtainCost $cost)
    {
        $prod = $cost->product;
        
        if ($prod) {
            // خصم الكمية من المخزون
            $prod->stock -= $cost->quantity;
            $prod->saveQuietly();

            // إنشاء سجل حركة المخزون
            StockTransaction::create([
                'product_id' => $prod->id,
                'quantity' => $cost->quantity,
                'type' => 'خارج',
                'reference_type' => curtainCost::class,
                'reference_id' => $cost->id,
            ]);
        }

        // تحديث حساب التكلفة والربح
        $this->recalculateSaleItem($cost);
    }

    /**
     * عند تحديث CurtainCost - إذا تغيرت الكمية، نرجع القديمة ونخصم الجديدة
     */
    public function updated(curtainCost $cost)
    {
        // إذا تغيرت الكمية أو المنتج
        if ($cost->wasChanged('quantity') || $cost->wasChanged('product_id')) {
            $oldQty = $cost->getOriginal('quantity');
            $newQty = $cost->quantity;
            
            // إذا تغير المنتج، نرجع الكمية القديمة من المنتج القديم
            if ($cost->wasChanged('product_id')) {
                $oldProductId = $cost->getOriginal('product_id');
                $oldProd = \App\Models\Product::find($oldProductId);
                if ($oldProd) {
                    $oldProd->stock += $oldQty;
                    $oldProd->saveQuietly();
                }
            } else {
                // نفس المنتج، نرجع الفرق
                $diff = $newQty - $oldQty;
                if ($diff != 0) {
                    $prod = $cost->product;
                    $prod->stock -= $diff; // إذا كانت الكمية الجديدة أكبر، نخصم الفرق
                    $prod->saveQuietly();
                }
            }

            // خصم الكمية الجديدة من المنتج الجديد
            if ($cost->wasChanged('product_id')) {
                $prod = $cost->product;
                $prod->stock -= $newQty;
                $prod->saveQuietly();
            }
        }

        // تحديث حساب التكلفة والربح
        $this->recalculateSaleItem($cost);
    }

    /**
     * عند حذف CurtainCost - نرجع الكمية للمخزون
     */
    public function deleted(curtainCost $cost)
    {
        $prod = $cost->product;
        
        if ($prod) {
            // إرجاع الكمية للمخزون
            $prod->stock += $cost->quantity;
            $prod->saveQuietly();
        }

        // تحديث حساب التكلفة والربح
        $this->recalculateSaleItem($cost);
    }

    /**
     * إعادة حساب التكلفة والربح للـ SaleItem
     */
    private function recalculateSaleItem(curtainCost $cost)
    {
        $item = $cost->sale_item;
        
        if ($item && $item->item_type === 'ستارة') {
            // إعادة حساب التكلفة الإجمالية من جميع curtainCosts + sewing_cost + extra_cost
            $totalCost = 0;
            
            foreach ($item->curtainCosts as $curtainCost) {
                $prod = $curtainCost->product;
                if ($prod) {
                    // حساب تكلفة المكون = الكمية × سعر تكلفة المنتج
                    $componentCost = $prod->cost_price * $curtainCost->quantity;
                    $totalCost += $componentCost;
                }
            }
            
            // إضافة تكاليف إضافية (sewing_cost + extra_cost)
            $totalCost += ($item->sewing_cost ?? 0) + ($item->extra_cost ?? 0);
            
            $item->total_cost = $totalCost;
            
            // حساب صافي الربح = سعر البيع للزبون - تكلفة الجملة
            // sell_price هو السعر الإجمالي للستارة (لا نضربه في الكمية)
            $item->profit = ($item->sell_price ?? 0) - $totalCost;
            $item->net_profit = $item->profit; // صافي الربح = الربح
            $item->saveQuietly();

            // تحديث الفاتورة
            $sale = $item->sale;
            if ($sale) {
                // حساب سعر البيع الإجمالي
                // للستارة: sell_price هو السعر الإجمالي (لا نضربه في الكمية)
                // للمنتج العادي: sell_price * quantity
                $sale->total_price = $sale->items()->get()->sum(function($item) {
                    if ($item->item_type === 'ستارة') {
                        return $item->sell_price; // للستارة: السعر الإجمالي
                    } else {
                        return $item->sell_price * $item->quantity; // للمنتج العادي: السعر × الكمية
                    }
                });
                $sale->total_cost = $sale->items()->sum('total_cost');
                $sale->profit = $sale->total_price - $sale->total_cost;
                $sale->saveQuietly();
            }
        }
    }
}
