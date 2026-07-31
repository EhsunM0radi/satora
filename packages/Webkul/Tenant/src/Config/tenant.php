<?php

return [
    /**
     * Tenant isolation mode:
     * - 'shared' — single database, tenant_id on all tables
     * - 'database' — separate database per tenant (requires stancl/tenancy)
     */
    'isolation' => 'shared',

    /**
     * Domains that bypass tenant resolution (central/install routes).
     */
    'central_domains' => [
        'satora.test',
        'localhost',
        '127.0.0.1',
    ],

    /**
     * Default theme and template for new tenants.
     */
    'default_theme' => 'minimal-luxury',
    'default_template' => 'fashion',
    'default_locale' => 'fa',

    /**
     * Trial period in days.
     */
    'trial_days' => 14,
];
