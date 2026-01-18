<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund_item extends Model
{
    protected $fillable = [
        'refund_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    public function refund()
    {
        return $this->belongsTo(Refund::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
