<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_id',
        'product_color_id',
        'description',
        'quantity',
        'unit_price',
    ];

    protected $appends = ['display_name', 'line_total'];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productColor(): BelongsTo
    {
        return $this->belongsTo(productColor::class, 'product_color_id');
    }

    /** حركة المخزون المرتبطة بهذا البند (إدخال بضاعة من الفاتورة) */
    public function stockTransaction(): MorphOne
    {
        return $this->morphOne(StockTransaction::class, 'reference');
    }

    protected static function booted(): void
    {
        static::created(function (InvoiceItem $item) {
            $item->syncStockTransaction();
        });

        static::updated(function (InvoiceItem $item) {
            if ($item->wasChanged(['product_id', 'product_color_id', 'quantity'])) {
                $item->stockTransaction?->delete();
                $item->syncStockTransaction();
            }
        });

        static::deleting(function (InvoiceItem $item) {
            $item->stockTransaction?->delete();
        });
    }

    /** إنشاء أو إعادة إنشاء حركة مخزون إدخال للبند (فقط إذا كان مرتبطاً بمنتج) */
    protected function syncStockTransaction(): void
    {
        if (! $this->product_id) {
            return;
        }
        StockTransaction::create([
            'product_id' => $this->product_id,
            'product_color_id' => $this->product_color_id,
            'quantity' => $this->quantity,
            'type' => StockTransaction::TYPE_IN,
            'reference_type' => self::class,
            'reference_id' => $this->id,
        ]);
    }

    public function getLineTotalAttribute(): float
    {
        return (float) $this->quantity * (float) $this->unit_price;
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->product_id && $this->product) {
            $name = $this->product->name;
            if ($this->product_color_id && $this->productColor) {
                $name .= ' - ' . $this->productColor->color;
            }
            return $name;
        }
        return $this->description ?? '—';
    }
}
