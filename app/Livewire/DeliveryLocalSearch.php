<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Delivery\DeliveryAvailabilityGate;
use Exception;
use Igniter\Flame\Geolite\Facades\Geocoder;
use Igniter\Flame\Geolite\GeoQuery;
use Igniter\Flame\Geolite\Model\Coordinates;
use Igniter\Local\Facades\Location;
use Igniter\Local\Models\Location as LocationModel;
use Igniter\Main\Traits\ConfigurableComponent;
use Igniter\Orange\Livewire\Concerns\SearchesNearby;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

final class DeliveryLocalSearch extends Component
{
    use ConfigurableComponent;
    use SearchesNearby {
        onSearchNearby as private performSearchNearby;
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
        if (! $this->searchAutocompleteEnabled) {
            return;
        }

        if (strlen($this->searchQuery) < 3) {
            $this->isSearching = false;
            $this->searchPoint = null;
            $this->dispatch('resetMap');

            return;
        }

        $this->isSearching = true;
        $query = GeoQuery::create($this->searchQuery);

        try {
            $suggestions = Geocoder::driver()->placesAutocomplete($query);
        } catch (Exception) {
            $this->reportProviderFailureAndThrow('autocomplete');
        }

        $this->placesSuggestions = $suggestions->toArray();
    }

    public function onSelectSuggestion(int $index): void
    {
        $suggestion = $this->placesSuggestions[$index] ?? null;
        if (! $this->searchAutocompleteEnabled || ! $suggestion) {
            return;
        }

        $this->isSearching = false;
        $this->searchQuery = $suggestion['title'] ?? null;

        if (array_get($suggestion, 'provider') === 'nominatim') {
            $placeCoordinates = new Coordinates(
                (float) $suggestion['data']['latitude'],
                (float) $suggestion['data']['longitude'],
            );
        } else {
            try {
                $placeCoordinates = Geocoder::driver('google')
                    ->getPlaceCoordinates(GeoQuery::create($suggestion['placeId']));
            } catch (Exception) {
                $this->reportProviderFailureAndThrow('place_lookup');
            }
        }

        $this->searchPoint = [$placeCoordinates->getLatitude(), $placeCoordinates->getLongitude()];

        $this->dispatch(
            'updateDeliveryLocationMap',
            lat: $this->searchPoint[0],
            lng: $this->searchPoint[1],
            geocoder: $suggestion['provider'],
        );
    }

    protected function geocodeSearchQuery($searchQuery)
    {
        try {
            $collection = Geocoder::geocode($searchQuery);
        } catch (Exception) {
            $this->reportProviderFailureAndThrow('forward_geocode');
        }

        return $this->handleGeocodeResponse($collection);
    }

    protected function geocodeSearchPoint($searchPoint)
    {
        throw_if(count(array_filter($searchPoint)) !== 2,
            ValidationException::withMessages([
                $this->searchField => lang('igniter.local::default.alert_no_search_query'),
            ]),
        );

        [$latitude, $longitude] = $searchPoint;

        try {
            $collection = Geocoder::reverse($latitude, $longitude);
        } catch (Exception) {
            $this->reportProviderFailureAndThrow('reverse_geocode');
        }

        $userLocation = $this->handleGeocodeResponse($collection);
        $this->searchQuery = $userLocation->getFormattedAddress();

        return $userLocation;
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

    private function reportProviderFailureAndThrow(string $operation): never
    {
        Log::warning('Delivery geocoder provider request failed.', [
            'event' => 'delivery_geocoder_provider_failure',
            'operation' => $operation,
        ]);

        $this->throwSanitizedGeocoderValidation();
    }
}
