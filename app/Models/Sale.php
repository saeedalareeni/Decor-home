<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'customer_id',
        'total_price',
        'total_cost',
        'sale_date',
        'profit',
        'notes'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(Sale_item::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function recalculateTotals()
    {
        $this->total_price = $this->items()->sum('total_price');
        $this->total_cost  = $this->items()->sum('total_cost');
        $this->net_profit  = $this->items()->sum('net_profit');

        $this->save();
    }
}
