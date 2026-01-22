<?php

use App\Jobs\GenerateRecurringExpensesJob;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('expenses:generate', function () {
    GenerateRecurringExpensesJob::dispatch();
})->describe('Generate monthly recurring expenses');

app(Schedule::class)->command('expenses:generate')
    ->everyMinute();