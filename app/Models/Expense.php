<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'title',
        'amount',
        'date',
        'expense_date',
        'notes',
    ];

    public function recurringExpense()
    {
        return $this->belongsTo(recurringExpense::class);
    }
}
