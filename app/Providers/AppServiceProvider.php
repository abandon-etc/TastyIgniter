<?php

namespace App\Providers;

use App\BirthdayBooking\BirthdayAvailabilityService;
use App\BirthdayBooking\BirthdayReservationRules;
use App\BirthdayBooking\BirthdayRules;
use App\Delivery\DeliveryApiWriteGuard;
use App\Delivery\DeliveryAvailabilityGate;
use App\Delivery\DeliveryCheckoutGuard;
use App\Delivery\DeliveryOrderTypeListener;
use App\Delivery\LocationDeliveryAction;
use App\Livewire\BirthdayReservation;
use App\Livewire\DeliveryLocalSearch;
use Igniter\Flame\Pagic\Router;
use Igniter\Local\Models\Location as LocationModel;
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
        $this->app->singleton(DeliveryAvailabilityGate::class);
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
        $this->app->booted(static function (): void {
            Livewire::component('igniter-orange::local-search', DeliveryLocalSearch::class);
        });

        LocationModel::implement(LocationDeliveryAction::class);

        Event::listen('location.orderType.updated', app(DeliveryOrderTypeListener::class)->handle(...));
        Event::listen('igniter.checkout.beforeSaveOrder', app(DeliveryCheckoutGuard::class)->handle(...));
        Event::listen('api.repository.beforeCreate', app(DeliveryApiWriteGuard::class)->handle(...));
        Event::listen('api.repository.beforeUpdate', app(DeliveryApiWriteGuard::class)->handle(...));

        if (! config('birthday_booking.enabled')) {
            return;
        }

        Event::listen('theme.getActiveTheme', static fn (): string => 'birthday-orange');

        app(BirthdayReservationRules::class)->register();
        Livewire::component('birthday-reservation', BirthdayReservation::class);
    }
}
