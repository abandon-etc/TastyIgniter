<?php

namespace App\Providers;

use App\BirthdayBooking\BirthdayAvailabilityService;
use App\BirthdayBooking\BirthdayReservationRules;
use App\BirthdayBooking\BirthdayRules;
use App\Livewire\BirthdayReservation;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(BirthdayRules::class);
        $this->app->singleton(BirthdayAvailabilityService::class);
        $this->app->singleton(BirthdayReservationRules::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (! config('birthday_booking.enabled')) {
            return;
        }

        app(BirthdayReservationRules::class)->register();
        Livewire::component('birthday-reservation', BirthdayReservation::class);
    }
}
