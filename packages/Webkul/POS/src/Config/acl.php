<?php

return [
    [
        'key' => 'sales.pos',
        'name' => 'pos::app.acl.pos',
        'route' => 'admin.pos.terminals.index',
        'sort' => 1,
    ],
    [
        'key' => 'sales.pos.terminals',
        'name' => 'pos::app.acl.terminals',
        'route' => 'admin.pos.terminals.index',
        'sort' => 1,
    ],
    [
        'key' => 'sales.pos.sessions',
        'name' => 'pos::app.acl.sessions',
        'route' => 'admin.pos.sessions.index',
        'sort' => 2,
    ],
    [
        'key' => 'sales.pos.orders',
        'name' => 'pos::app.acl.orders',
        'route' => 'admin.pos.orders.index',
        'sort' => 3,
    ],
    [
        'key' => 'settings.pos',
        'name' => 'pos::app.acl.settings',
        'route' => 'admin.pos.settings.index',
        'sort' => 1,
    ],
];
