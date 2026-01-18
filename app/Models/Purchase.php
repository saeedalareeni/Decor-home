<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_name',
        'total_amount',
    ];

    public function items()
    {
        return $this->hasMany(Purchase_item::class);
    }
}
