<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Delivery\DeliveryAvailabilityGate;
use Closure;
use Igniter\Flame\Support\Facades\Igniter;
use Igniter\Local\Classes\Location;
use Illuminate\Http\Request;

final class NormalizeDeliveryFulfillment
{
    public function __construct(
        private readonly DeliveryAvailabilityGate $gate,
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        if (! Igniter::runningInAdmin() && Igniter::hasDatabase()) {
            /** @var Location $location */
            $location = resolve('location');
            $location->currentOrDefault();
            $this->gate->normalizeOrderType($location);
        }

        return $next($request);
    }
}
