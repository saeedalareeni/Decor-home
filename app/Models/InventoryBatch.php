<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBatch extends Model
{
    protected $fillable = [
        'product_id',
        'product_color_id',
        'invoice_item_id',
        'cost_price',
        'quantity_in',
        'quantity_remaining',
        'received_at',
        'notes',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'quantity_in' => 'decimal:2',
        'quantity_remaining' => 'decimal:2',
        'received_at' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productColor(): BelongsTo
    {
        return $this->belongsTo(productColor::class, 'product_color_id');
    }

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    /** Label for dropdown: سعر الجملة: X ₪ (متبقي: Y) */
    public function getBatchLabelAttribute(): string
    {
        return sprintf(
            'سعر الجملة: %s ₪ (متبقي: %s)',
            number_format((float) $this->cost_price, 2),
            number_format((float) $this->quantity_remaining, 2)
        );
    }
}
