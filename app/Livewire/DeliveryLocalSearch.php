<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Delivery\DeliveryAvailabilityGate;
use Igniter\Local\Facades\Location;
use Igniter\Local\Models\Location as LocationModel;
use Igniter\Main\Traits\ConfigurableComponent;
use Igniter\Orange\Livewire\Concerns\SearchesNearby;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Throwable;

final class DeliveryLocalSearch extends Component
{
    use ConfigurableComponent;
    use SearchesNearby {
        onSearchNearby as private performSearchNearby;
        onSelectSuggestion as private performSelectSuggestion;
        updatedSearchQuery as private performUpdatedSearchQuery;
    }

    public bool $hideSearch = false;

    public static function componentMeta(): array
    {
        return [
            'code' => 'igniter-orange::local-search',
            'name' => 'igniter.orange::default.component_local_search_title',
            'description' => 'igniter.orange::default.component_local_search_desc',
        ];
    }

    public function defineProperties(): array
    {
        return array_merge([
            'hideSearch' => [
                'label' => 'Hide the search field and display a view menu button.',
                'type' => 'switch',
            ],
        ], $this->definePropertiesSearchNearby());
    }

    public function render(): View
    {
        return view('igniter-orange::livewire.local-search');
    }

    public function onSearchNearby()
    {
        $gate = resolve(DeliveryAvailabilityGate::class);
        $gate->assertDeliveryEnabled(Location::currentOrDefault());

        $response = $this->performSearchNearby();

        $gate->assertDeliveryEnabled(Location::currentOrDefault());
        Location::updateOrderType(LocationModel::DELIVERY);

        return $response;
    }

    public function updatedSearchQuery(): void
    {
        try {
            $this->performUpdatedSearchQuery();
        } catch (Throwable) {
            $this->reportProviderFailureAndThrow();
        }
    }

    public function onSelectSuggestion(int $index): void
    {
        try {
            $this->performSelectSuggestion($index);
        } catch (Throwable) {
            $this->reportProviderFailureAndThrow();
        }
    }

    protected function handleGeocodeResponse($collection)
    {
        if (! $collection || $collection->isEmpty()) {
            Log::warning('Delivery geocoder returned no usable result.', [
                'event' => 'delivery_geocoder_empty_result',
            ]);

            $this->throwSanitizedGeocoderValidation();
        }

        $userLocation = $collection->first();
        if (! $userLocation->hasCoordinates()) {
            $this->throwSanitizedGeocoderValidation();
        }

        return $userLocation;
    }

    private function throwSanitizedGeocoderValidation(): never
    {
        throw ValidationException::withMessages([
            $this->searchField => lang('igniter.local::default.alert_invalid_search_query'),
        ]);
    }

    private function reportProviderFailureAndThrow(): never
    {
        Log::warning('Delivery geocoder provider request failed.', [
            'event' => 'delivery_geocoder_provider_failure',
        ]);

        $this->throwSanitizedGeocoderValidation();
    }
}
