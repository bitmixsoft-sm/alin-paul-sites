<?php

namespace App\Providers;

use App\RouletteValue;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        // The framework's own pagination default switched from Bootstrap 4 to Tailwind
        // in this Laravel version - the admin theme is Bootstrap 4, not Tailwind, so
        // without this the pager renders unstyled, oversized Tailwind SVG icons.
        Paginator::useBootstrapFour();

        view()->composer(['find_friends', 'roulette'], function ($view) {

            // $title = l('Roulette');
            $title = l('Find Friends');
            $rouletteOptions = RouletteValue::all('display_text');

            $view->with([
                'title' => $title,
                'canSpin' => config('app.debug') || !optional(Auth::user())->hasDiscount(),
                'rouletteOptions' => $rouletteOptions
            ]);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}