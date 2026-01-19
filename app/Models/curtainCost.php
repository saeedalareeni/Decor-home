<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class curtainCost extends Model
{
    protected $fillable = [
        'sale_item_id',
        'product_id',
        'quantity',
        "product_color_id"
    ];

    public function sale_item()
    {
        return $this->belongsTo(Sale_item::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
