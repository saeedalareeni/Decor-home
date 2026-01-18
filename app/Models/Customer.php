<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'phone',
    ];
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
