<?php

return [
    [
        'key' => 'sales.pos',
        'name' => 'pos::app.admin.menu.pos',
        'route' => 'admin.pos.terminals.index',
        'sort' => 1,
        'icon' => 'icon-point-of-sale',
    ],
    [
        'key' => 'sales.pos.sessions',
        'name' => 'pos::app.admin.menu.sessions',
        'route' => 'admin.pos.sessions.index',
        'sort' => 2,
        'icon' => '',
    ],
    [
        'key' => 'sales.pos.orders',
        'name' => 'pos::app.admin.menu.orders',
        'route' => 'admin.pos.orders.index',
        'sort' => 3,
        'icon' => '',
    ],
    [
        'key' => 'settings.pos',
        'name' => 'pos::app.admin.menu.settings',
        'route' => 'admin.pos.settings.index',
        'sort' => 1,
        'icon' => 'icon-settings',
    ],
];
