<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Validation\ValidationException;

class curtainCost extends Model
{
    protected $fillable = [
        'sale_item_id',
        'product_id',
        'product_color_id',
        'quantity',
        'inventory_batch_id',
        'consumed_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'consumed_cost' => 'decimal:2',
    ];

    public function sale_item()
    {
        return $this->belongsTo(Sale_item::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productColor()
    {
        return $this->belongsTo(productColor::class, 'product_color_id');
    }

    public function inventoryBatch()
    {
        return $this->belongsTo(\App\Models\InventoryBatch::class, 'inventory_batch_id');
    }

    public function stockTransaction(): MorphOne
    {
        return $this->morphOne(StockTransaction::class, 'reference');
    }

    protected static function booted(): void
    {
        static::creating(function (curtainCost $cost) {
            $available = static::getAvailableStock($cost->product_id, $cost->product_color_id);
            $needed = (float) ($cost->quantity ?? 0);
            if ($available < $needed) {
                $productName = $cost->product?->name ?? 'المنتج';
                throw ValidationException::withMessages([
                    'quantity' => ["المخزون غير كافٍ للمكوّن ({$productName}). المتاح: {$available}، المطلوب: {$needed}"],
                ]);
            }
        });

        static::updating(function (curtainCost $cost) {
            $oldProductId = (int) $cost->getOriginal('product_id');
            $oldColorId = $cost->getOriginal('product_color_id');
            $oldColorId = $oldColorId ? (int) $oldColorId : null;
            $oldQty = (float) $cost->getOriginal('quantity');
            $newProductId = (int) $cost->product_id;
            $newColorId = $cost->product_color_id ? (int) $cost->product_color_id : null;
            $newQty = (float) ($cost->quantity ?? 0);

            $productChanged = $oldProductId !== $newProductId || $oldColorId !== $newColorId;

            if ($productChanged) {
                $available = static::getAvailableStock($newProductId, $newColorId);
            } else {
                $available = static::getAvailableStock($newProductId, $newColorId);
                $available += $oldQty;
            }

            if ($available < $newQty) {
                $productName = $cost->product?->name ?? 'المنتج';
                throw ValidationException::withMessages([
                    'quantity' => ["المخزون غير كافٍ للمكوّن ({$productName}). المتاح: " . round($available, 2) . "، المطلوب: {$newQty}"],
                ]);
            }
        });
    }

    private static function getAvailableStock(?int $productId, ?int $productColorId): float
    {
        if (! $productId) {
            return 0;
        }
        if ($productColorId) {
            $color = productColor::find($productColorId);

            return (float) ($color->stock ?? 0);
        }
        $product = Product::find($productId);

        return (float) ($product->stock ?? 0);
    }
}
