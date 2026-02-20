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

    public function recalculateTotal(): void
    {
        $this->total_amount = $this->items()->get()->sum(fn (InvoiceItem $item) => (float) $item->quantity * (float) $item->unit_price);
        $this->save();
    }
}
