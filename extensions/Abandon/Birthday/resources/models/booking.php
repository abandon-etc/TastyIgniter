<?php

$config['list']['toolbar'] = ['buttons' => []];
$config['list']['bulkActions'] = [];

$config['list']['columns'] = [
    'preview' => [
        'type' => 'button',
        'iconCssClass' => 'fa fa-eye',
        'attributes' => [
            'class' => 'btn btn-edit',
            'href' => 'abandon/birthday/bookings/preview/{birthday_booking_id}',
        ],
    ],
    'public_id' => [
        'label' => 'abandon.birthday::default.booking_labels.public_id',
        'type' => 'text',
        'searchable' => true,
    ],
    'customer_name' => [
        'label' => 'abandon.birthday::default.booking_labels.customer',
        'type' => 'text',
        'sortable' => false,
    ],
    'contact_name' => [
        'label' => 'abandon.birthday::default.booking_labels.contact_name',
        'type' => 'text',
        'searchable' => false,
    ],
    'event_date' => [
        'label' => 'abandon.birthday::default.booking_labels.event_date',
        'type' => 'date',
    ],
    'slot_label' => [
        'label' => 'abandon.birthday::default.booking_labels.slot',
        'type' => 'text',
        'sortable' => false,
    ],
    'package_name_snapshot' => [
        'label' => 'abandon.birthday::default.booking_labels.package',
        'type' => 'text',
    ],
    'addons_count' => [
        'label' => 'abandon.birthday::default.booking_labels.addon_count',
        'type' => 'number',
        'sortable' => false,
    ],
    'catalog_subtotal_display' => [
        'label' => 'abandon.birthday::default.booking_labels.catalog_subtotal',
        'type' => 'text',
        'sortable' => false,
    ],
    'currency' => [
        'label' => 'abandon.birthday::default.label_currency',
        'type' => 'text',
    ],
    'status_label' => [
        'label' => 'abandon.birthday::default.booking_labels.status',
        'type' => 'text',
        'sortable' => false,
    ],
    'created_at' => [
        'label' => 'lang:igniter::admin.column_date_added',
        'type' => 'datetime',
    ],
];

$config['form']['toolbar'] = ['buttons' => []];
$config['form']['fields'] = [
    'public_id' => ['label' => 'abandon.birthday::default.booking_labels.public_id', 'type' => 'text', 'disabled' => true],
    'customer_name' => ['label' => 'abandon.birthday::default.booking_labels.customer', 'type' => 'text', 'disabled' => true, 'span' => 'left'],
    'contact_name' => ['label' => 'abandon.birthday::default.booking_labels.contact_name', 'type' => 'text', 'disabled' => true, 'span' => 'right'],
    'contact_email_snapshot' => ['label' => 'abandon.birthday::default.booking_labels.contact_email', 'type' => 'text', 'disabled' => true, 'span' => 'left'],
    'contact_telephone_snapshot' => ['label' => 'abandon.birthday::default.booking_labels.contact_telephone', 'type' => 'text', 'disabled' => true, 'span' => 'right'],
    'location_name' => ['label' => 'abandon.birthday::default.booking_labels.location', 'type' => 'text', 'disabled' => true],
    'event_date' => ['label' => 'abandon.birthday::default.booking_labels.event_date', 'type' => 'text', 'disabled' => true, 'span' => 'left'],
    'slot_label' => ['label' => 'abandon.birthday::default.booking_labels.slot', 'type' => 'text', 'disabled' => true, 'span' => 'right'],
    'guest_count' => ['label' => 'abandon.birthday::default.booking_labels.guest_count', 'type' => 'number', 'disabled' => true],
    'package_name_snapshot' => ['label' => 'abandon.birthday::default.booking_labels.package', 'type' => 'text', 'disabled' => true],
    'package_description_snapshot' => ['label' => 'abandon.birthday::default.booking_labels.package_description', 'type' => 'textarea', 'disabled' => true],
    'package_included_items_text' => ['label' => 'abandon.birthday::default.label_included_items', 'type' => 'textarea', 'disabled' => true],
    'addon_summary' => ['label' => 'abandon.birthday::default.booking_labels.addons', 'type' => 'textarea', 'disabled' => true],
    'package_price_display' => ['label' => 'abandon.birthday::default.booking_labels.package_subtotal', 'type' => 'text', 'disabled' => true, 'span' => 'left'],
    'addons_subtotal_display' => ['label' => 'abandon.birthday::default.booking_labels.addons_subtotal', 'type' => 'text', 'disabled' => true, 'span' => 'right'],
    'catalog_subtotal_display' => ['label' => 'abandon.birthday::default.booking_labels.catalog_subtotal', 'type' => 'text', 'disabled' => true, 'span' => 'left'],
    'currency' => ['label' => 'abandon.birthday::default.label_currency', 'type' => 'text', 'disabled' => true, 'span' => 'right'],
    'pricing_version' => ['label' => 'abandon.birthday::default.booking_labels.pricing_version', 'type' => 'number', 'disabled' => true, 'span' => 'left'],
    'status_label' => ['label' => 'abandon.birthday::default.booking_labels.status', 'type' => 'text', 'disabled' => true, 'span' => 'right'],
    'priced_at' => ['label' => 'abandon.birthday::default.booking_labels.priced_at', 'type' => 'text', 'disabled' => true, 'span' => 'left'],
    'cancelled_at' => ['label' => 'abandon.birthday::default.booking_labels.cancelled_at', 'type' => 'text', 'disabled' => true, 'span' => 'right'],
    'created_at' => ['label' => 'lang:igniter::admin.column_date_added', 'type' => 'text', 'disabled' => true],
];

return $config;
