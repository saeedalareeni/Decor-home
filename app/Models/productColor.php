<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class productColor extends Model
{
    protected $fillable = [
        "color",
        "stock",
        "product_id"
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::created(function ($item) {
            if ($item->stock > 0 && $item->product) {
                $item->product->increment('stock', $item->stock);
            }
        });

        static::updated(function ($item) {
            if ($item->wasChanged('stock') && $item->product) {
                $old = (float) $item->getOriginal('stock');
                $new = (float) $item->stock;
                $diff = $new - $old;

                if ($diff > 0) {
                    $item->product->increment('stock', $diff); // كمية زائدة → زيادة المخزون
                } else {
                    $item->product->decrement('stock', abs($diff)); // كمية أقل → خصم المخزون
                }

            }
        });

        static::deleting(function ($item) {
            if ($item->stock > 0 && $item->product) {
                $item->product->decrement('stock', $item->stock);
            }
        });
    }
}
