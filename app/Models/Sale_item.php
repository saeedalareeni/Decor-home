<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Validation\ValidationException;

class Sale_item extends Model
{
    protected $fillable = [
        'product_color_id',
        'sale_id',
        'product_id',
        'item_type',
        'quantity',
        'sell_price',
        'total_cost',
        'profit',
        'net_profit',
        'sewing_cost',
        'extra_cost',
        'notes',
        'inventory_batch_id',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function colors(): BelongsTo
    {
        return $this->belongsTo(productColor::class, 'product_color_id');
    }

    public function inventoryBatch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }

    /** حركة المخزون المرتبطة بهذا البند (إخراج بضاعة من البيع) */
    public function stockTransaction(): MorphOne
    {
        return $this->morphOne(StockTransaction::class, 'reference');
    }

    public function curtainCosts()
    {
        return $this->hasMany(CurtainCost::class, 'sale_item_id');
    }

    public function components()
    {
        return $this->hasMany(CurtainCost::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Sale_item $item) {
            if ($item->item_type === 'منتج عادي' && $item->product_id) {
                $available = static::getAvailableStock($item->product_id, $item->product_color_id);
                $needed = (float) ($item->quantity ?? 0);
                if ($available < $needed) {
                    throw ValidationException::withMessages([
                        'quantity' => ['المخزون غير كافٍ. المتاح: ' . $available . '، المطلوب: ' . $needed],
                    ]);
                }
            }
        });

        static::updating(function (Sale_item $item) {
            if ($item->item_type !== 'منتج عادي' || ! $item->product_id) {
                return;
            }
            $oldProductId = (int) $item->getOriginal('product_id');
            $oldColorId = $item->getOriginal('product_color_id');
            $oldColorId = $oldColorId ? (int) $oldColorId : null;
            $oldQty = (float) $item->getOriginal('quantity');
            $newProductId = (int) $item->product_id;
            $newColorId = $item->product_color_id ? (int) $item->product_color_id : null;
            $newQty = (float) ($item->quantity ?? 0);

            $productChanged = $oldProductId !== $newProductId || $oldColorId !== $newColorId;

            if ($productChanged) {
                $available = static::getAvailableStock($newProductId, $newColorId);
            } else {
                $available = static::getAvailableStock($newProductId, $newColorId);
                $available += $oldQty;
            }

            if ($available < $newQty) {
                throw ValidationException::withMessages([
                    'quantity' => ['المخزون غير كافٍ. المتاح: ' . round($available, 2) . '، المطلوب: ' . $newQty],
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
