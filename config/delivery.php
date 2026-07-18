<?php

declare(strict_types=1);

use App\Delivery\DeliveryFlag;

return [
    'enabled' => DeliveryFlag::parse(env('DELIVERY_ENABLED', false)),
];
