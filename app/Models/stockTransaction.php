<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    protected $fillable = [
        'product_id',
        'product_color_id',
        'quantity',
        'type',
        'reference_type',
        'reference_id',
    ];
}
