<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'supplier_name',
        'invoice_number',
        'invoice_date',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /** المبلغ الإجمالي يُحسب من مجموع تكلفة البنود (كمية × سعر الوحدة) */
    public function recalculateTotalFromItems(): void
    {
        $total = (float) $this->items()->get()->sum(fn ($item) => (float) $item->quantity * (float) $item->unit_price);
        $this->total_amount = round($total, 2);
        $this->saveQuietly();
    }
}
