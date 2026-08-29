<?php

namespace App\Providers;

use App\BirthdayBooking\BirthdayAvailabilityService;
use App\BirthdayBooking\BirthdayMigrationOrder;
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
use App\Mail\MailTestRedirect;
use App\Support\DefaultLocaleIntegrity;
use App\Support\TimezoneIntegrity;
use Igniter\System\Models\Language;
use Igniter\System\Models\Settings;
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

        // Test revisions only: when MAIL_TEST_REDIRECT_TO is set, every mailer
        // this deployment resolves gets Mailer::alwaysTo() that address, so no
        // message can reach a real recipient. Applied during registration so
        // it precedes any mailer being built. See App\Mail\MailTestRedirect.
        MailTestRedirect::apply($this->app['config']);

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
        // The local extension registers before every vendor extension and
        // migration groups run in registration order, so on a fresh database
        // abandon.birthday would migrate before the igniter extensions whose
        // tables it references. Move its group to the end of the map; the
        // group key, and with it the migrations ledger identity, stays the
        // same. See App\BirthdayBooking\BirthdayMigrationOrder.
        BirthdayMigrationOrder::apply();

        $this->app->booted(static function (): void {
            Livewire::component('igniter-orange::local-search', DeliveryLocalSearch::class);
        });

        // Registered after TastyIgniter's own booted callback, which is
        // what sets the running timezone, so this observes the outcome
        // rather than racing it. See App\Support\TimezoneIntegrity.
        $this->app->booted(static function (): void {
            TimezoneIntegrity::report(
                Settings::get('timezone'),
                date_default_timezone_get(),
                (string) config('app.timezone'),
            );

            // The default language is what gives untagged model columns
            // their meaning, so changing it reinterprets existing menu
            // content instead of failing. See App\Support\DefaultLocaleIntegrity.
            DefaultLocaleIntegrity::report(Language::getDefault()?->code);
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
