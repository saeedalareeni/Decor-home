<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sale_id',
        'refund_number',
        'total_refund_amount',
        'refund_method',
        'reason',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function items()
    {
        return $this->hasMany(Refund_item::class);
    }
}
