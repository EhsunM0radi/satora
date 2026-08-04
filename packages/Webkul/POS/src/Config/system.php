<?php

return [
    [
        'key' => 'sales.pos',
        'name' => 'pos::app.admin.system.pos',
        'sort' => 5,
    ],
    [
        'key' => 'sales.pos.general',
        'name' => 'pos::app.admin.system.general',
        'sort' => 1,
        'fields' => [
            [
                'name' => 'default_currency',
                'title' => 'pos::app.admin.system.default-currency',
                'type' => 'select',
                'channel_based' => true,
                'locale_based' => true,
            ],
            [
                'name' => 'stock_reservation_ttl',
                'title' => 'pos::app.admin.system.stock-reservation-ttl',
                'type' => 'text',
                'validation' => 'integer|min:1|max:120',
                'channel_based' => true,
            ],
        ],
    ],
    [
        'key' => 'sales.pos.receipt',
        'name' => 'pos::app.admin.system.receipt',
        'sort' => 2,
        'fields' => [
            [
                'name' => 'receipt_logo',
                'title' => 'pos::app.admin.system.receipt-logo',
                'type' => 'image',
                'channel_based' => true,
            ],
            [
                'name' => 'receipt_footer',
                'title' => 'pos::app.admin.system.receipt-footer',
                'type' => 'textarea',
                'channel_based' => true,
            ],
        ],
    ],
];
