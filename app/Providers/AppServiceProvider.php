<?php

namespace App\Providers;

use App\Models\Purchase_item;
use App\Models\Refund_item;
use App\Models\Sale_item;
use App\Observers\Purchase_itemObserver;
use App\Observers\Refund_itemObserver;
use App\Observers\Sale_itemObserver;
use Illuminate\Support\ServiceProvider;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sale_item::observe(Sale_itemObserver::class);
        Refund_item::observe(Refund_itemObserver::class);
        Purchase_item::observe(Purchase_itemObserver::class);

        // LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
        //     $switch->locales(['ar', 'en'])->labels([
        //         "ar" => "العربية",
        //         "en" => "English"
        //     ]);
        // });
    }
}
