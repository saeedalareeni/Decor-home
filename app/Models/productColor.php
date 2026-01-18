<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class productColor extends Model
{
    protected $fillable = [
        "color",
        "stock"
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::saving(function ($item) {
            $count = ProductColor::where("product_id", $item->product_id)->get();
            if (count($count) == 0) {
                $item->product->stock = $item->stock;
                $item->product->save();
            } else {
                $item->product->stock += $item->stock;
                $item->product->save();
            }
        });

        static::deleting(function ($item) {
            $item->product->decrement("stock", $item->stock);
        });

        static::updating(function ($item) {
            if ($item->wasChanged('stock')) {
                $old = $item->getOriginal('stock');
                $new = $item->stock;
                $diff = $new - $old;

                if ($diff > 0) {
                    $item->product->increment('stock', $diff);
                } elseif ($diff < 0) {
                    $item->product->decrement('stock', abs($diff));
                }
            }
        });
    }
}
