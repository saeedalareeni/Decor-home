<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class stockTransaction extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'type',
        'reference_type',
        'reference_id',
    ];
}
