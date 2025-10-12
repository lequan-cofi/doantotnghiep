<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Date;
use App\Models\BookingDeposit;
use App\Models\Lease;
use App\Models\LeaseService;
use App\Models\Viewing;
use App\Observers\BookingDepositObserver;
use App\Observers\LeaseObserver;
use App\Observers\LeaseServiceObserver;
use App\Observers\ViewingObserver;
// CommissionEventObserver removed - no longer creating invoices for commission events

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
        
        // Register model observers for automatic invoice updates
        BookingDeposit::observe(BookingDepositObserver::class);
        Lease::observe(LeaseObserver::class);
        LeaseService::observe(LeaseServiceObserver::class);
        Viewing::observe(ViewingObserver::class);
        // CommissionEventObserver removed - commission events no longer create invoices
    }
}
