<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class recurringExpense extends Model
{
    protected $fillable = [
        'name',
        'amount',
        'frequency',
        'start_date',
        'end_date',
    ];

    public function generateExpense()
    {
        return Expense::create([
            'title' => $this->name,
            'amount' => $this->amount,
            'expense_date' => now(),
            'notes' => 'مصروف شهري متكرر',
        ]);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
