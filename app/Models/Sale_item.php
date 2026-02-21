<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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
        'extra_cost',
        'notes'
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function colors(): BelongsTo
    {
        return $this->belongsTo(productColor::class, 'product_color_id');
    }

    /** حركة المخزون المرتبطة بهذا البند (إخراج بضاعة من البيع) */
    public function stockTransaction(): MorphOne
    {
        return $this->morphOne(StockTransaction::class, 'reference');
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
