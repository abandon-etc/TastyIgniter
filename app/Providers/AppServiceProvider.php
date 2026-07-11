<?php

namespace App\Providers;

use App\BirthdayBooking\BirthdayAvailabilityService;
use App\BirthdayBooking\BirthdayReservationRules;
use App\BirthdayBooking\BirthdayRules;
use App\Livewire\BirthdayReservation;
use Igniter\Flame\Pagic\Router;
use Illuminate\Support\Facades\Event;
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

        if (config('birthday_booking.enabled')) {
            $this->app->afterResolving(Router::class, function (Router $router): void {
                $router->setTheme('birthday-orange');
            });
        }
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

        Event::listen('theme.getActiveTheme', static fn(): string => 'birthday-orange');

        app(BirthdayReservationRules::class)->register();
        Livewire::component('birthday-reservation', BirthdayReservation::class);
    }
}
