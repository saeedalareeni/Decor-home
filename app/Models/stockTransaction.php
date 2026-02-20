<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockTransaction extends Model
{
    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';

    protected $fillable = [
        'product_id',
        'product_color_id',
        'quantity',
        'type',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productColor(): BelongsTo
    {
        return $this->belongsTo(productColor::class, 'product_color_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function booted(): void
    {
        static::created(function (StockTransaction $transaction) {
            $qty = (float) $transaction->quantity;
            if ($transaction->type === self::TYPE_OUT) {
                $qty = -$qty;
            }
            if ($transaction->product_color_id && $transaction->productColor) {
                $transaction->productColor->increment('stock', $qty);
            } elseif ($transaction->product) {
                $transaction->product->increment('stock', $qty);
            }
        });

        static::updated(function (StockTransaction $transaction) {
            if (! $transaction->wasChanged(['quantity', 'type', 'product_id', 'product_color_id'])) {
                return;
            }
            $oldQty = (float) $transaction->getOriginal('quantity');
            $oldType = $transaction->getOriginal('type');
            $newQty = (float) $transaction->quantity;
            $newType = $transaction->type;
            $oldColorId = $transaction->getOriginal('product_color_id');
            $oldProductId = $transaction->getOriginal('product_id');

            // Reverse old effect
            $reverse = $oldType === self::TYPE_OUT ? $oldQty : -$oldQty;
            if ($oldColorId) {
                $oldColor = productColor::find($oldColorId);
                if ($oldColor) {
                    $oldColor->increment('stock', $reverse);
                }
            } elseif ($oldProductId) {
                $oldProduct = Product::find($oldProductId);
                if ($oldProduct) {
                    $oldProduct->increment('stock', $reverse);
                }
            }

            // Apply new effect
            $apply = $newType === self::TYPE_IN ? $newQty : -$newQty;
            if ($transaction->product_color_id && $transaction->productColor) {
                $transaction->productColor->increment('stock', $apply);
            } elseif ($transaction->product) {
                $transaction->product->increment('stock', $apply);
            }
        });

        static::deleting(function (StockTransaction $transaction) {
            $qty = (float) $transaction->quantity;
            if ($transaction->type === self::TYPE_IN) {
                $qty = -$qty;
            } elseif ($transaction->type === self::TYPE_OUT) {
                // reversing an out = add back
                $qty = abs($qty);
            }
            if ($transaction->product_color_id && $transaction->productColor) {
                $transaction->productColor->increment('stock', $qty);
            } elseif ($transaction->product) {
                $transaction->product->increment('stock', $qty);
            }
        });
    }
}
