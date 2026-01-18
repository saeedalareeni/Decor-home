<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'unit',
        'stock',
        'cost_price',
        'selling_price',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems()
    {
        return $this->hasMany(Sale_item::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(Stock_movement::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(Purchase_item::class);
    }

    public function ProductColor(){
        return $this->hasMany(ProductColor::class);
    }
}
