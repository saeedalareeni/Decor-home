<?php
namespace App\Jobs;

use App\Models\RecurringExpense;
use App\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateRecurringExpensesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $today = now()->toDateString();

        $recurrings = RecurringExpense::where('start_date', '<=', $today)
            ->where(function($q) use ($today) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $today);
            })
            ->where('frequency', 'monthly')
            ->get();

        foreach ($recurrings as $recurring) {
            $already = Expense::where('recurring_expense_id', $recurring->id)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->exists();

            if (!$already) {
                Expense::create([
                    'recurring_expense_id' => $recurring->id,
                    'title' => $recurring->name,
                    'amount' => $recurring->amount,
                    'date' => now(),
                    'type' => 'مصروف دوري',
                ]);
            }
        }
    }
}
