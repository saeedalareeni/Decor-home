<?php

namespace App\Observers;

use App\Models\Sale_item;
use App\Models\SaleItem;
use App\Models\StockTransaction;

class SaleItemObserver
{
    public function created(Sale_item $item)
    {

        if ($item->item_type === 'ستارة') {
            // للستارة، حساب التكلفة وخصم المخزون يحدث في CurtainCostObserver
            // عند إضافة كل مكون في الـ Repeater
            // هنا نحسب فقط إذا كان هناك sewing_cost أو extra_cost
            // sell_price هو السعر الإجمالي للستارة (لا نضربه في الكمية)
            $totalCost = ($item->sewing_cost ?? 0) + ($item->extra_cost ?? 0);
            $item->total_cost = $totalCost;
            $item->profit = $item->sell_price - $totalCost; // الربح = سعر البيع الإجمالي - التكلفة
            $item->net_profit = $item->profit; // صافي الربح = الربح
            $item->saveQuietly();
        } else {

            $prod = $item->product;
            $item->total_cost = $prod->cost_price * $item->quantity;
            $salesPrice = $item->sell_price * $item->quantity; // سعر البيع الإجمالي للزبون
            $item->profit = $salesPrice - $item->total_cost; // الربح = سعر البيع - التكلفة
            $item->net_profit = $item->profit; // صافي الربح = الربح (نفس الشيء)
            $item->saveQuietly();

            // إذا كان هناك product_color_id، نخصم من مخزون اللون
            // البوتد في productColor سيخصم من المخزون الإجمالي تلقائياً
            // وإلا نخصم من مخزون المنتج العام مباشرة
            if ($item->product_color_id) {
                // خصم من مخزون اللون (الـ booted سيحدث المخزون الإجمالي)
                $productColor = \App\Models\productColor::find($item->product_color_id);
                if ($productColor) {
                    $productColor->stock -= $item->quantity;
                    $productColor->save(); // الـ booted سيعدل المخزون الإجمالي تلقائياً
                }
            } else {
                // لا يوجد لون محدد، نخصم من المنتج العام مباشرة
                \App\Models\Product::where('id', $prod->id)
                    ->decrement('stock', $item->quantity);
            }

            StockTransaction::create([
                'product_id' => $prod->id,
                'quantity' => $item->quantity,
                'type' => 'خارج',
                'reference_type' => Sale_item::class,
                'reference_id' => $item->id,
            ]);
        }

        // تحديث الفاتورة
        $sale = $item->sale;
        // حساب سعر البيع الإجمالي
        // للستارة: sell_price هو السعر الإجمالي (لا نضربه في الكمية)
        // للمنتج العادي: sell_price * quantity
        $sale->total_price = $sale->items()->get()->sum(function ($item) {
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

    public function updated(Sale_item $item)
    {
        if ($item->item_type === 'ستارة') {
            // للستارة، حساب التكلفة وخصم المخزون يحدث في CurtainCostObserver
            // عند إضافة كل مكون في الـ Repeater
            // هنا نحسب فقط إذا كان هناك sewing_cost أو extra_cost
            // sell_price هو السعر الإجمالي للستارة (لا نضربه في الكمية)
            $totalCost = ($item->sewing_cost ?? 0) + ($item->extra_cost ?? 0);
            $item->total_cost = $totalCost;
            $item->profit = $item->sell_price - $totalCost; // الربح = سعر البيع الإجمالي - التكلفة
            $item->net_profit = $item->profit; // صافي الربح = الربح
            $item->saveQuietly();
        } else {

            $prod = $item->product;

            // حساب الفرق في الكمية
            $originalQuantity = $item->getOriginal('quantity');
            $currentQuantity = $item->quantity;
            $quantityDifference = $currentQuantity - $originalQuantity;

            $item->total_cost = $prod->cost_price * $item->quantity;
            $salesPrice = $item->sell_price * $item->quantity; // سعر البيع الإجمالي للزبون
            $item->profit = $salesPrice - $item->total_cost; // الربح = سعر البيع - التكلفة
            $item->net_profit = $item->profit; // صافي الربح = الربح (نفس الشيء)
            $item->saveQuietly();

            // تحديث المخزون بناءً على الفرق في الكمية
            // إذا كان هناك product_color_id، نحدث من مخزون اللون
            // والـ booted في productColor سيحدث المخزون الإجمالي تلقائياً
            // وإلا نحدث من مخزون المنتج العام مباشرة
            if ($item->product_color_id) {
                // تحديث مخزون اللون
                $productColor = \App\Models\productColor::find($item->product_color_id);
                if ($productColor) {
                    $productColor->stock = $productColor->stock - ($currentQuantity - $originalQuantity);
                    $productColor->save(); // الـ booted سيعدل المخزون الإجمالي تلقائياً
                }
            } else {
                // لا يوجد لون محدد، نحدث من المنتج العام مباشرة
                if ($quantityDifference > 0) {
                    // زيادة في الكمية = نقص إضافي في المخزون
                    \App\Models\Product::where('id', $prod->id)
                        ->decrement('stock', $quantityDifference);
                } else {
                    // نقص في الكمية = إرجاع جزء للمخزون
                    \App\Models\Product::where('id', $prod->id)
                        ->increment('stock', abs($quantityDifference));
                }
            }

            // تحديث StockTransaction
            $transaction = StockTransaction::where('reference_type', Sale_item::class)
                ->where('reference_id', $item->id)
                ->first();

            if ($transaction) {
                $transaction->quantity = $currentQuantity;
                $transaction->save();
            }
        }

        // تحديث الفاتورة
        $sale = $item->sale;
        // حساب سعر البيع الإجمالي
        // للستارة: sell_price هو السعر الإجمالي (لا نضربه في الكمية)
        // للمنتج العادي: sell_price * quantity
        $sale->total_price = $sale->items()->get()->sum(function ($item) {
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

    public function deleted(Sale_item $item)
    {
        if ($item->item_type === 'ستارة') {
            // للستارة، ستتم معالجة الحذف في CurtainCostObserver
        } else {
            $prod = $item->product;

            // إرجاع الكمية إلى المخزون
            if ($item->product_color_id) {
                // إرجاع من مخزون اللون (الـ booted سيحدث المخزون الإجمالي)
                $productColor = \App\Models\productColor::find($item->product_color_id);
                if ($productColor) {
                    $productColor->stock += $item->quantity;
                    $productColor->save(); // الـ booted سيعدل المخزون الإجمالي تلقائياً
                }
            } else {
                // لا يوجد لون محدد، نرجع فقط من المنتج العام
                \App\Models\Product::where('id', $prod->id)
                    ->increment('stock', $item->quantity);
            }

            // حذف StockTransaction
            StockTransaction::where('reference_type', Sale_item::class)
                ->where('reference_id', $item->id)
                ->delete();
        }

        // تحديث الفاتورة
        $sale = $item->sale;
        if ($sale) {
            $sale->total_price = $sale->items()->get()->sum(function ($item) {
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
