<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Date;

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
        // Set timezone for Carbon/Date
        Date::setLocale('vi');
        
        // Ensure all dates use the app timezone
        config(['app.timezone' => config('app.timezone', 'Asia/Ho_Chi_Minh')]);
        date_default_timezone_set(config('app.timezone'));
    }
}
