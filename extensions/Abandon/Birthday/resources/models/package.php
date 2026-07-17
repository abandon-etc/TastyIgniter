<?php

$config['list']['toolbar'] = [
    'buttons' => [
        'create' => [
            'label' => 'lang:igniter::admin.button_new',
            'class' => 'btn btn-primary',
            'href' => 'abandon/birthday/packages/create',
        ],
        'archived' => [
            'label' => 'abandon.birthday::default.button_archived',
            'class' => 'btn btn-outline-secondary',
            'href' => 'abandon/birthday/packages?show_archived=1',
        ],
    ],
];

$config['list']['columns'] = [
    'edit' => [
        'type' => 'button',
        'iconCssClass' => 'fa fa-pencil',
        'attributes' => ['class' => 'btn btn-edit', 'href' => 'abandon/birthday/packages/edit/{birthday_package_id}'],
    ],
    'name' => ['label' => 'abandon.birthday::default.label_name', 'type' => 'text', 'searchable' => true],
    'price' => ['label' => 'abandon.birthday::default.label_price', 'type' => 'text'],
    'currency' => ['label' => 'abandon.birthday::default.label_currency', 'type' => 'text'],
    'is_default' => ['label' => 'abandon.birthday::default.label_default', 'type' => 'switch'],
    'is_enabled' => ['label' => 'abandon.birthday::default.label_enabled', 'type' => 'switch'],
    'archived_at' => ['label' => 'abandon.birthday::default.label_archived', 'type' => 'datetime'],
    'sort_order' => ['label' => 'abandon.birthday::default.label_sort_order', 'type' => 'text'],
    'updated_at' => ['label' => 'lang:igniter::admin.column_date_updated', 'type' => 'datetime'],
    'birthday_package_id' => ['label' => 'lang:igniter::admin.column_id', 'invisible' => true],
];

$config['form']['toolbar'] = [
    'buttons' => [
        'save' => [
            'label' => 'lang:igniter::admin.button_save',
            'context' => ['create', 'edit'],
            'partial' => 'form/toolbar_save_button',
            'class' => 'btn btn-primary',
            'data-request' => 'onSave',
        ],
        'archive' => [
            'label' => 'abandon.birthday::default.button_archive',
            'context' => ['edit'],
            'class' => 'btn btn-outline-danger',
            'data-request' => 'onArchive',
            'data-request-confirm' => 'abandon.birthday::default.confirm_archive',
        ],
        'restore' => [
            'label' => 'abandon.birthday::default.button_restore',
            'context' => ['edit'],
            'class' => 'btn btn-outline-success',
            'data-request' => 'onRestore',
        ],
    ],
];

$config['form']['tabs'] = [
    'defaultTab' => 'abandon.birthday::default.text_tab_general',
    'fields' => [
        'name' => ['label' => 'abandon.birthday::default.label_name', 'type' => 'text', 'span' => 'left'],
        'price' => ['label' => 'abandon.birthday::default.label_price', 'type' => 'text', 'span' => 'left', 'cssClass' => 'flex-width'],
        'currency' => ['label' => 'abandon.birthday::default.label_currency', 'type' => 'text', 'span' => 'right', 'default' => 'CAD', 'attributes' => ['readonly' => true]],
        'description' => ['label' => 'abandon.birthday::default.label_description', 'type' => 'textarea'],
        'included_items_text' => ['label' => 'abandon.birthday::default.label_included_items', 'type' => 'textarea', 'comment' => 'abandon.birthday::default.help_included_items'],
        'is_default' => ['label' => 'abandon.birthday::default.label_default', 'type' => 'switch', 'span' => 'left'],
        'is_enabled' => ['label' => 'abandon.birthday::default.label_enabled', 'type' => 'switch', 'span' => 'right', 'default' => true],
        'sort_order' => ['label' => 'abandon.birthday::default.label_sort_order', 'type' => 'number', 'span' => 'left', 'default' => 0],
    ],
];

return $config;
