<?php

namespace App\Providers;

use App\BirthdayBooking\BirthdayAvailabilityService;
use App\BirthdayBooking\BirthdayReservationRules;
use App\BirthdayBooking\BirthdayRules;
use App\Delivery\DeliveryApiWriteGuard;
use App\Delivery\DeliveryAvailabilityGate;
use App\Delivery\DeliveryCheckoutGuard;
use App\Delivery\DeliveryOrderType;
use App\Delivery\DeliveryOrderTypeListener;
use App\Delivery\GeocoderChainOverride;
use App\Delivery\LocationDeliveryAction;
use App\Delivery\WeekdayScheduleCorrection;
use App\Livewire\BirthdayReservation;
use App\Livewire\DeliveryLocalSearch;
use Igniter\Cart\OrderTypes\Delivery as VendorDeliveryOrderType;
use Igniter\Flame\Pagic\Router;
use Igniter\Local\Events\WorkingScheduleCreatedEvent;
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

        // The vendor builds every order type through the container
        // (OrderTypes::makeOrderTypes resolves each class), so binding the
        // vendor's Delivery class to the project's subclass is enough for the
        // storefront to receive it everywhere. See App\Delivery\DeliveryOrderType.
        $this->app->bind(VendorDeliveryOrderType::class, DeliveryOrderType::class);

        // afterResolving, not resolving: TastyIgniter's own resolving callback
        // rewrites the geocoder configuration from the settings table, and
        // afterResolving is guaranteed to run once every resolving callback
        // has, without depending on provider registration order.
        $this->app->afterResolving('geocoder', static function ($geocoder, $app): void {
            GeocoderChainOverride::apply($app['config']);
        });
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

        // Rebuilds working-schedule periods with the stored Monday-first weekday
        // convention. See App\Delivery\WeekdayScheduleCorrection.
        Event::listen(WorkingScheduleCreatedEvent::class, app(WeekdayScheduleCorrection::class)->handle(...));

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
