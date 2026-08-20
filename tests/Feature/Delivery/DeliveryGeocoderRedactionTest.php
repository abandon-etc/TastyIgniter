<?php

declare(strict_types=1);

namespace Tests\Feature\Delivery;

use App\Livewire\DeliveryLocalSearch;
use Igniter\Flame\Geolite\Facades\Geocoder;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Mockery;
use ReflectionMethod;
use RuntimeException;
use Tests\TestCase;

final class DeliveryGeocoderRedactionTest extends TestCase
{
    private const PRIVATE_ADDRESS_MARKER = 'Q011_PRIVATE_ADDRESS_MARKER';

    private const PRIVATE_CREDENTIAL_MARKER = 'Q011_PRIVATE_CREDENTIAL_MARKER';

    private const PRIVATE_PROVIDER_URL = 'https://provider.example.invalid/geocode?address='.
        self::PRIVATE_ADDRESS_MARKER.'&key='.self::PRIVATE_CREDENTIAL_MARKER;

    public function test_empty_geocode_result_logs_only_a_generic_event(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->with('Delivery geocoder returned no usable result.', [
                'event' => 'delivery_geocoder_empty_result',
            ]);
        Log::shouldReceive('error')->never();

        $component = new DeliveryLocalSearch;
        $method = new ReflectionMethod($component, 'handleGeocodeResponse');

        try {
            $method->invoke($component, collect());
            $this->fail('Expected the empty geocode result to fail closed.');
        } catch (ValidationException $exception) {
            $this->assertSanitizedValidation($exception);
        }
    }

    public function test_autocomplete_provider_failure_returns_a_generic_livewire_error(): void
    {
        $this->expectGenericProviderFailureLog();

        $geocoder = Mockery::mock();
        $geocoder->shouldReceive('driver')
            ->once()
            ->withNoArgs()
            ->andThrow(new RuntimeException(self::PRIVATE_PROVIDER_URL));
        Geocoder::swap($geocoder);

        $component = new DeliveryLocalSearch;
        $component->searchQuery = self::PRIVATE_ADDRESS_MARKER;

        try {
            $component->updatedSearchQuery();
            $this->fail('Expected autocomplete failure to fail closed.');
        } catch (ValidationException $exception) {
            $this->assertSanitizedValidation($exception);
        }
    }

    public function test_place_lookup_provider_failure_returns_a_generic_livewire_error(): void
    {
        $this->expectGenericProviderFailureLog();

        $geocoder = Mockery::mock();
        $geocoder->shouldReceive('driver')
            ->once()
            ->with('google')
            ->andThrow(new RuntimeException(self::PRIVATE_PROVIDER_URL));
        Geocoder::swap($geocoder);

        $component = new DeliveryLocalSearch;
        $component->placesSuggestions = [[
            'provider' => 'google',
            'placeId' => 'synthetic-place-id',
            'title' => self::PRIVATE_ADDRESS_MARKER,
        ]];

        try {
            $component->onSelectSuggestion(0);
            $this->fail('Expected place lookup failure to fail closed.');
        } catch (ValidationException $exception) {
            $this->assertSanitizedValidation($exception);
        }
    }

    private function assertSanitizedValidation(ValidationException $exception): void
    {
        $payload = json_encode($exception->errors(), JSON_THROW_ON_ERROR);

        $this->assertSame(
            [lang('igniter.local::default.alert_invalid_search_query')],
            $exception->errors()['searchQuery'],
        );
        $this->assertStringNotContainsString(self::PRIVATE_ADDRESS_MARKER, $payload);
        $this->assertStringNotContainsString(self::PRIVATE_CREDENTIAL_MARKER, $payload);
        $this->assertStringNotContainsString('provider.example.invalid', $payload);
    }

    private function expectGenericProviderFailureLog(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->with('Delivery geocoder provider request failed.', [
                'event' => 'delivery_geocoder_provider_failure',
            ]);
        Log::shouldReceive('error')->never();
    }
}
