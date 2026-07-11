<?php

return [
    'enabled' => (bool) env('BIRTHDAY_BOOKING_RULES_ENABLED', false),

    'timezone' => env('BIRTHDAY_BOOKING_TIMEZONE', 'America/Toronto'),

    'min_advance_days' => 2,

    'max_advance_days' => 60,

    'slots' => [
        [
            'code' => '12-16',
            'start' => '12:00',
            'end' => '16:00',
            'label' => 'birthday_booking.slots.12_16',
            'capacity' => 1,
        ],
        [
            'code' => '16-20',
            'start' => '16:00',
            'end' => '20:00',
            'label' => 'birthday_booking.slots.16_20',
            'capacity' => 1,
        ],
    ],
];
