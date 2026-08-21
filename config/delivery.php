<?php

declare(strict_types=1);

use App\Delivery\DeliveryFlag;

return [
    'enabled' => DeliveryFlag::parse(env('DELIVERY_ENABLED', false)),

    // Provider selection for this deployment. Null means "use the stored
    // configuration unchanged", which is the default in every environment.
    // An empty providers string is a deliberate empty list, not "unset".
    'geocoder' => [
        'driver' => env('DELIVERY_GEOCODER_DRIVER'),
        'providers' => env('DELIVERY_GEOCODER_PROVIDERS'),
    ],
];
