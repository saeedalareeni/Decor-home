<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'type',
        'stock',
        'cost_price',
        'selling_price',
        'number_of_layers',
        'supplier_name',
    ];


protected $appends = ['total_cost'];

public function getTotalCostAttribute()
{
    return $this->cost_price * $this->stock;
}
    public function saleItems()
    {
        return $this->hasMany(Sale_item::class);
    }

    // public function stockMovements()
    // {
    //     return $this->hasMany(Stock_movement::class);
    // }

    public function purchaseItems()
    {
        return $this->hasMany(Purchase_item::class);
    }

    public function colors()
    {
        return $this->hasMany(productColor::class);
    }

    // public function recipe()
    // {
    //     return $this->hasMany(CurtainRecipe::class, 'curtain_product_id');
    // }
}
