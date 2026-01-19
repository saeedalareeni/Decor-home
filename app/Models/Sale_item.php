<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale_item extends Model
{
    protected $fillable = [
        'product_color_id',
        'sale_id',
        'product_id',
        'item_type',
        'quantity',
        'sell_price',
        'total_cost',
        'profit',
        'net_profit',
        'sewing_cost',
        'extra_cost'
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function colors()
    {
        return $this->belongsTo(productColor::class);
    }

    public function curtainCosts()
    {
        return $this->hasMany(CurtainCost::class, 'sale_item_id');
    }

    public function components()
    {
        return $this->hasMany(CurtainCost::class);
    }
}
