<?php

declare(strict_types=1);

namespace Tests\Feature\Delivery;

use App\Delivery\Exceptions\DeliveryUnavailableException;
use App\Livewire\DeliveryLocalSearch;
use Igniter\Local\Classes\Location as LocationService;
use Igniter\Local\Models\Location;
use Igniter\Local\Models\LocationSettings;
use Igniter\Main\Classes\ThemeManager;
use Igniter\Main\Models\Theme;
use Igniter\Main\Template\Page;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

final class DeliveryStorefrontUiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Theme::syncAll();
        $themeManager = resolve(ThemeManager::class);
        $themeManager->loadThemes();
        $theme = $themeManager->findTheme('birthday-orange');
        $this->assertNotNull($theme);
        $theme->active = true;
        $themeManager->bootTheme($theme);
        Page::getSourceResolver()->setDefaultSourceName('birthday-orange');
        Event::listen(
            'main.page.beforeRoute',
            static fn (string $fileName): ?Page => Page::load('birthday-orange', $fileName),
        );
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('birthday_booking.enabled', true);
        $app['config']->set('igniter-system.locationMode', Location::LOCATION_CONTEXT_SINGLE);
    }

    public function test_project_local_search_component_replaces_the_upstream_component(): void
    {
        config()->set('delivery.enabled', false);
        $this->setCurrentLocation(true, true);

        $component = Livewire::test('igniter-orange::local-search');

        $this->assertInstanceOf(DeliveryLocalSearch::class, $component->instance());
    }

    public function test_closed_state_homepage_is_pickup_only_and_keeps_birthday_entry(): void
    {
        config()->set('delivery.enabled', false);
        $this->setCurrentLocation(true, true);

        $component = Livewire::test('igniter-orange::local-search');

        $component
            ->assertSeeText(trans('delivery.order_for_pickup'))
            ->assertSeeText(trans('delivery.book_birthday_venue'))
            ->assertDontSee('delivery-search-query', false)
            ->assertDontSeeText(trans('delivery.check_delivery_availability'));
    }

    public function test_enabled_state_homepage_shows_accessible_search_and_keeps_both_ctas(): void
    {
        config()->set('delivery.enabled', true);
        $this->setCurrentLocation(true, true);

        $component = Livewire::test('igniter-orange::local-search');

        $component
            ->assertSee('id="delivery-search-query"', false)
            ->assertSee('for="delivery-search-query"', false)
            ->assertSee('aria-label="'.trans('delivery.use_current_location').'"', false)
            ->assertSeeText(trans('delivery.enter_delivery_address'))
            ->assertSeeText(trans('delivery.check_delivery_availability'))
            ->assertSeeText(trans('delivery.order_for_pickup'))
            ->assertSeeText(trans('delivery.book_birthday_venue'));
    }

    public function test_location_level_disable_hides_delivery_search_even_when_global_flag_is_on(): void
    {
        config()->set('delivery.enabled', true);
        $this->setCurrentLocation(false, true);

        $component = Livewire::test('igniter-orange::local-search');

        $component
            ->assertDontSee('delivery-search-query', false)
            ->assertSeeText(trans('delivery.order_for_pickup'))
            ->assertSeeText(trans('delivery.book_birthday_venue'));
    }

    public function test_no_fulfillment_state_has_no_fake_food_cta(): void
    {
        config()->set('delivery.enabled', false);
        $this->setCurrentLocation(true, false);

        $component = Livewire::test('igniter-orange::local-search');

        $component
            ->assertSeeText(trans('delivery.no_fulfillment_available'))
            ->assertDontSeeText(trans('delivery.order_for_pickup'))
            ->assertDontSee('delivery-search-query', false)
            ->assertSeeText(trans('delivery.book_birthday_venue'));
    }

    public function test_closed_state_removes_delivery_schedule_tab_from_the_menu_info_panel(): void
    {
        config()->set('delivery.enabled', false);
        $this->setCurrentLocation(true, true);

        $html = view('igniter-orange::includes.local.hours', [
            'locationInfo' => $this->makeScheduleViewData(),
        ])->render();

        $this->assertStringNotContainsString('id="delivery-tab"', $html);
        $this->assertStringContainsString('id="collection-tab"', $html);
        $this->assertStringContainsString('id="opening-tab"', $html);
    }

    public function test_enabled_state_exposes_only_schedule_tabs_for_available_fulfillment_types(): void
    {
        config()->set('delivery.enabled', true);
        $this->setCurrentLocation(true, false);

        $html = view('igniter-orange::includes.local.hours', [
            'locationInfo' => $this->makeScheduleViewData('delivery'),
        ])->render();

        $this->assertStringContainsString('id="delivery-tab"', $html);
        $this->assertStringNotContainsString('id="collection-tab"', $html);
        $this->assertStringContainsString('id="opening-tab"', $html);
        $this->assertStringContainsString('aria-selected="true"', $html);
    }

    public function test_no_fulfillment_state_keeps_only_general_opening_hours(): void
    {
        config()->set('delivery.enabled', false);
        $this->setCurrentLocation(true, false);

        $html = view('igniter-orange::includes.local.hours', [
            'locationInfo' => $this->makeScheduleViewData('delivery'),
        ])->render();

        $this->assertStringContainsString('id="opening-tab"', $html);
        $this->assertStringNotContainsString('id="delivery-tab"', $html);
        $this->assertStringNotContainsString('id="collection-tab"', $html);
        $this->assertStringContainsString('aria-selected="true"', $html);
    }

    public function test_fulfillment_timeslot_uses_valid_alpine_tuple_syntax(): void
    {
        $html = view('igniter-orange::includes.local.timeslot', [
            'showAsapOption' => true,
            'showLaterOption' => true,
            'isAsap' => true,
            'previewMode' => false,
            'timeslotDates' => ['2026-07-20' => 'July 20'],
        ])->render();

        $this->assertStringContainsString(
            'x-for="(value, key) in timeslot[orderDate]"',
            $html,
        );
        $this->assertStringNotContainsString(
            'x-for="value, key in timeslot[orderDate]"',
            $html,
        );
        $this->assertStringContainsString('wire:ignore', $html);
    }

    public function test_hidden_search_action_still_fails_closed_server_side(): void
    {
        config()->set('delivery.enabled', false);
        $this->setCurrentLocation(true, true);
        $component = Livewire::test('igniter-orange::local-search');

        $this->expectException(DeliveryUnavailableException::class);

        $component->instance()->onSearchNearby();
    }

    public function test_required_canadian_english_and_french_copy_is_available(): void
    {
        $expectations = [
            'en_CA' => [
                'delivery.delivery' => 'Delivery',
                'delivery.pickup' => 'Pickup',
                'delivery.enter_delivery_address' => 'Enter your delivery address',
                'delivery.check_delivery_availability' => 'Check delivery availability',
                'delivery.order_for_pickup' => 'Order for pickup',
                'delivery.book_birthday_venue' => 'Book a birthday venue',
                'delivery.delivery_unavailable' => 'Delivery is currently unavailable',
                'delivery.no_fulfillment_available' => 'No fulfillment method is available',
            ],
            'fr_CA' => [
                'delivery.delivery' => 'Livraison',
                'delivery.pickup' => 'Cueillette',
                'delivery.enter_delivery_address' => 'Entrez votre adresse de livraison',
                'delivery.check_delivery_availability' => 'Vérifier la disponibilité de la livraison',
                'delivery.order_for_pickup' => 'Commander pour cueillette',
                'delivery.book_birthday_venue' => 'Réserver une salle pour un anniversaire',
                'delivery.delivery_unavailable' => 'La livraison est actuellement indisponible',
                'delivery.no_fulfillment_available' => 'Aucun mode de commande n’est disponible',
            ],
        ];

        foreach ($expectations as $locale => $copy) {
            app()->setLocale($locale);

            foreach ($copy as $key => $value) {
                $this->assertSame($value, trans($key));
            }
        }
    }

    private function setCurrentLocation(bool $deliveryEnabled, bool $collectionEnabled): LocationService
    {
        $model = Location::getDefault() ?? Location::query()->firstOrFail();
        $model->loadMissing('settings');
        $settings = $model->settings
            ->reject(fn (LocationSettings $setting): bool => in_array($setting->item, ['delivery', 'collection'], true))
            ->values();
        $settings->push(
            new LocationSettings([
                'item' => 'delivery',
                'data' => ['is_enabled' => $deliveryEnabled],
            ]),
            new LocationSettings([
                'item' => 'collection',
                'data' => ['is_enabled' => $collectionEnabled],
            ]),
        );
        $model->setRelation('settings', new EloquentCollection($settings->all()));

        /** @var LocationService $location */
        $location = resolve('location');
        $location->clearInternalCache();
        $location->setSessionKey('delivery_storefront_ui_test');
        $location->setModel($model);

        return $location;
    }

    private function makeScheduleViewData(string $activeType = 'collection'): object
    {
        return new class($activeType)
        {
            public function __construct(private readonly string $activeType) {}

            public function scheduleTypes(): array
            {
                return [
                    'opening' => ['name' => 'delivery.pickup'],
                    'delivery' => ['name' => 'delivery.delivery'],
                    'collection' => ['name' => 'delivery.pickup'],
                ];
            }

            public function scheduleItems(): array
            {
                $hours = ['Monday' => [['open' => '12:00', 'close' => '20:00']]];

                return [
                    'opening' => $hours,
                    'delivery' => $hours,
                    'collection' => $hours,
                ];
            }

            public function orderType(): object
            {
                return new class($this->activeType)
                {
                    public function __construct(private readonly string $activeType) {}

                    public function getCode(): string
                    {
                        return $this->activeType;
                    }
                };
            }
        };
    }
}
