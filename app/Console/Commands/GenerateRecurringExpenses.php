<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Models\recurringExpense;
use Illuminate\Console\Command;
use App\Jobs\GenerateRecurringExpensesJob;

class GenerateRecurringExpenses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expenses:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly recurring expenses';

    /**
     * Execute the console command.
     */
     public function handle()
    {
        GenerateRecurringExpensesJob::dispatch();

        $this->info('Recurring expenses job dispatched.');
    }
}
