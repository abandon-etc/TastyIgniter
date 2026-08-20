<?php

declare(strict_types=1);

namespace Tests\Feature\Delivery;

use App\Livewire\DeliveryLocalSearch;
use Error;
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
        $this->expectGenericProviderFailureLog('autocomplete');

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
        $this->expectGenericProviderFailureLog('place_lookup');

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

    public function test_forward_geocode_exception_returns_a_generic_validation_error(): void
    {
        $this->expectGenericProviderFailureLog('forward_geocode');

        $geocoder = Mockery::mock();
        $geocoder->shouldReceive('geocode')
            ->once()
            ->with(self::PRIVATE_ADDRESS_MARKER)
            ->andThrow(new RuntimeException(self::PRIVATE_PROVIDER_URL));
        Geocoder::swap($geocoder);

        $component = new DeliveryLocalSearch;
        $method = new ReflectionMethod($component, 'geocodeSearchQuery');

        try {
            $method->invoke($component, self::PRIVATE_ADDRESS_MARKER);
            $this->fail('Expected forward geocode failure to fail closed.');
        } catch (ValidationException $exception) {
            $this->assertSanitizedValidation($exception);
        }
    }

    public function test_reverse_geocode_exception_returns_a_generic_validation_error(): void
    {
        $this->expectGenericProviderFailureLog('reverse_geocode');

        $geocoder = Mockery::mock();
        $geocoder->shouldReceive('reverse')
            ->once()
            ->with(1.25, 2.5)
            ->andThrow(new RuntimeException(self::PRIVATE_PROVIDER_URL));
        Geocoder::swap($geocoder);

        $component = new DeliveryLocalSearch;
        $method = new ReflectionMethod($component, 'geocodeSearchPoint');

        try {
            $method->invoke($component, [1.25, 2.5]);
            $this->fail('Expected reverse geocode failure to fail closed.');
        } catch (ValidationException $exception) {
            $this->assertSanitizedValidation($exception);
        }
    }

    public function test_programming_error_is_not_converted_to_address_validation(): void
    {
        Log::shouldReceive('warning')->never();
        Log::shouldReceive('error')->never();

        $geocoder = Mockery::mock();
        $geocoder->shouldReceive('driver')
            ->once()
            ->withNoArgs()
            ->andThrow(new Error('Q011_PROGRAMMING_ERROR_MARKER'));
        Geocoder::swap($geocoder);

        $component = new DeliveryLocalSearch;
        $component->searchQuery = self::PRIVATE_ADDRESS_MARKER;

        $this->expectException(Error::class);
        $this->expectExceptionMessage('Q011_PROGRAMMING_ERROR_MARKER');

        $component->updatedSearchQuery();
    }

    public function test_reverse_geocode_preserves_invalid_coordinate_business_validation(): void
    {
        Log::shouldReceive('warning')->never();
        Log::shouldReceive('error')->never();

        $component = new DeliveryLocalSearch;
        $method = new ReflectionMethod($component, 'geocodeSearchPoint');

        try {
            $method->invoke($component, [1.25]);
            $this->fail('Expected incomplete coordinates to preserve business validation.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                [lang('igniter.local::default.alert_no_search_query')],
                $exception->errors()['searchQuery'],
            );
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

    private function expectGenericProviderFailureLog(string $operation): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->with('Delivery geocoder provider request failed.', [
                'event' => 'delivery_geocoder_provider_failure',
                'operation' => $operation,
            ]);
        Log::shouldReceive('error')->never();
    }
}
