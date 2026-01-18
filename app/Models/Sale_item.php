<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale_item extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'ring_cost',
        'tailor_cost',
        'extra_cost',
        'net_profit',
        'product_color_id',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productColor()
    {
        return $this->belongsTo(productColor::class);
    }

    protected static function booted()
    {
        static::saving(function ($item) {
            $quantity  = (float) $item->quantity;
            $unitPrice = (float) $item->unit_price;

            $fabric = (float) $item->fabric_cost;
            $ring   = (float) $item->ring_cost;
            $tailor = (float) $item->tailor_cost;
            $extra  = (float) $item->extra_cost;

            $item->total_price = $quantity * $unitPrice;

            $item->total_cost =
                $fabric +
                $ring +
                $tailor +
                $extra;

            $item->net_profit = $item->total_price - (($quantity * $item->product->cost_price) + $item->total_cost);
        });

        static::created(function ($item) {

            if ($item->product_color_id) {
                // خصم من مخزون اللون
                // dd($item->quantity);
                $item->productColor->decrement('stock', $item->quantity);
            } else {
                // خصم من مخزون المنتج
                $item->product->decrement('stock', $item->quantity);
            }
        });
    }
}
