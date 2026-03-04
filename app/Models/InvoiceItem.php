<?php

namespace App\Models;

use App\Models\InventoryBatch;
use App\Services\InventoryService;
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
            try {
                $item->syncInventoryBatch();
            } catch (\InvalidArgumentException $e) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'quantity' => [$e->getMessage()],
                ]);
            }
            $item->invoice?->recalculateTotalFromItems();
        });

        static::updated(function (InvoiceItem $item) {
            try {
                if ($item->wasChanged(['product_id', 'product_color_id', 'quantity'])) {
                    $item->stockTransaction?->delete();
                    $item->syncStockTransaction();
                    $item->syncInventoryBatch();
                } elseif ($item->wasChanged('unit_price')) {
                    $item->syncInventoryBatch();
                }
            } catch (\InvalidArgumentException $e) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'quantity' => [$e->getMessage()],
                ]);
            }
            if ($item->wasChanged(['quantity', 'unit_price', 'product_id', 'product_color_id'])) {
                $item->invoice?->recalculateTotalFromItems();
            }
        });

        static::deleting(function (InvoiceItem $item) {
            $item->stockTransaction?->delete();
        });

        static::deleted(function (InvoiceItem $item) {
            Invoice::find($item->invoice_id)?->recalculateTotalFromItems();
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

    /** إنشاء أو تحديث دفعة مخزون للبند (سعر الجملة) عند الإدخال من الفاتورة */
    protected function syncInventoryBatch(): void
    {
        if (! $this->product_id) {
            return;
        }
        $receivedAt = $this->invoice?->invoice_date ?? now();
        $quantity = (float) $this->quantity;
        $batch = InventoryBatch::where('invoice_item_id', $this->id)->first();

        if ($batch) {
            $oldIn = (float) $batch->quantity_in;
            $oldRemaining = (float) $batch->quantity_remaining;
            $delta = $quantity - $oldIn;
            $newRemaining = $oldRemaining + $delta;

            // إذا الدفعة اتباعت منها جزء (quantity_remaining < quantity_in): لا نعدّل cost_price، فقط الكمية بالفرق
            $hasPartialSales = $oldRemaining < $oldIn;

            if ($hasPartialSales) {
                if ($newRemaining < 0) {
                    throw new \InvalidArgumentException(
                        'لا يمكن تخفيض كمية البند إلى أقل من الكمية المباعة من هذه الدفعة. المتبقي حالياً: ' . $oldRemaining
                    );
                }
                if ($newRemaining > $quantity) {
                    $newRemaining = $quantity;
                }
                $batch->update([
                    'quantity_in' => $quantity,
                    'quantity_remaining' => $newRemaining,
                    'received_at' => $receivedAt,
                ]);
            } else {
                $costPrice = (float) $this->unit_price > 0
                    ? (float) $this->unit_price
                    : (float) ($this->product?->cost_price ?? 0);
                $batch->update([
                    'cost_price' => $costPrice,
                    'quantity_in' => $quantity,
                    'quantity_remaining' => max(0, $oldRemaining + $delta),
                    'received_at' => $receivedAt,
                ]);
            }
        } else {
            $costPrice = (float) $this->unit_price > 0
                ? (float) $this->unit_price
                : (float) ($this->product?->cost_price ?? 0);
            app(InventoryService::class)->addBatch(
                (int) $this->product_id,
                $this->product_color_id ? (int) $this->product_color_id : null,
                $quantity,
                $costPrice,
                (int) $this->id,
                $receivedAt,
                null
            );
        }
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
