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

    /** المبلغ الإجمالي يُدخله المستخدم في الفاتورة، لا يُحسب من البنود */
    public function recalculateTotal(): void
    {
        // لا نعدّل total_amount — يُدخل من نموذج الفاتورة فقط
    }
}
