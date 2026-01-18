<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase_item extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'cost_price',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
