<?php

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Webkul\Tenant\Models\Tenant;
use Webkul\User\Models\Admin;

// ── Tenant Model Tests ──

test('tenant can be created with required fields', function () {
    $tenant = Tenant::create([
        'name' => 'Test Store',
        'slug' => 'test-store',
    ]);

    expect($tenant)->toBeInstanceOf(Tenant::class);
    expect($tenant->name)->toBe('Test Store');
    expect($tenant->slug)->toBe('test-store');
    expect($tenant->exists)->toBeTrue();
});

test('tenant slug must be unique', function () {
    Tenant::create([
        'name' => 'Store One',
        'slug' => 'unique-slug',
    ]);

    Tenant::create([
        'name' => 'Store Two',
        'slug' => 'unique-slug',
    ]);
})->throws(QueryException::class);

test('tenant settings is cast to array', function () {
    $tenant = Tenant::create([
        'name' => 'Test Store',
        'slug' => 'settings-test',
        'settings' => ['theme' => 'dark', 'currency' => 'IRT'],
    ]);

    $fresh = Tenant::find($tenant->id);

    expect($fresh->settings)->toBeArray();
    expect($fresh->settings)->toHaveKeys(['theme', 'currency']);
    expect($fresh->settings['theme'])->toBe('dark');
});

test('tenant modules is cast to array', function () {
    $tenant = Tenant::create([
        'name' => 'Test Store',
        'slug' => 'modules-test',
        'modules' => ['inventory', 'crm', 'blog'],
    ]);

    $fresh = Tenant::find($tenant->id);

    expect($fresh->modules)->toBeArray();
    expect($fresh->modules)->toContain('inventory');
    expect($fresh->modules)->toContain('crm');
});

test('tenant is_active defaults to true', function () {
    $tenant = Tenant::create([
        'name' => 'Test Store',
        'slug' => 'active-test',
    ]);

    $fresh = Tenant::find($tenant->id);

    expect($fresh->is_active)->toBeTrue();
});

test('tenant can be created as inactive', function () {
    $tenant = Tenant::create([
        'name' => 'Inactive Store',
        'slug' => 'inactive-test',
        'is_active' => false,
    ]);

    $fresh = Tenant::find($tenant->id);

    expect($fresh->is_active)->toBeFalse();
});

test('tenant trial_ends_at is nullable datetime', function () {
    // Without trial_ends_at — should be null
    $tenant = Tenant::create([
        'name' => 'Test Store',
        'slug' => 'no-trial',
    ]);

    expect($tenant->trial_ends_at)->toBeNull();

    // With trial_ends_at — should be Carbon instance
    $tenant2 = Tenant::create([
        'name' => 'Trial Store',
        'slug' => 'with-trial',
        'trial_ends_at' => now()->addDays(14),
    ]);

    expect($tenant2->trial_ends_at)->toBeInstanceOf(Carbon::class);
});

test('tenant has admin relationship via tenant_user pivot', function () {
    $tenant = Tenant::create([
        'name' => 'Test Store',
        'slug' => 'relation-test',
    ]);

    $admin = Admin::create([
        'name' => 'Admin User',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'status' => 1,
        'role_id' => 1,
    ]);

    $tenant->users()->attach($admin->id, ['role' => 'tenant_admin']);

    expect($tenant->users)->toHaveCount(1);
    expect($tenant->users->first()->id)->toBe($admin->id);
    expect($tenant->users->first()->pivot->role)->toBe('tenant_admin');
});

test('tenant admin has tenants relationship', function () {
    $tenant = Tenant::create([
        'name' => 'Test Store',
        'slug' => 'admin-tenant-test',
    ]);

    $admin = Admin::create([
        'name' => 'Admin User',
        'email' => 'admin2@test.com',
        'password' => bcrypt('password'),
        'status' => 1,
        'role_id' => 1,
    ]);

    $admin->tenants()->attach($tenant->id, ['role' => 'tenant_admin']);

    expect($admin->tenants)->toHaveCount(1);
    expect($admin->tenants->first()->id)->toBe($tenant->id);
});

test('tenant interface methods work', function () {
    $tenant = Tenant::create([
        'name' => 'Interface Test Store',
        'slug' => 'interface-test-store',
        'domain' => 'test.satora.test',
        'theme' => 'minimal-luxury',
        'template' => 'fashion',
        'locale' => 'fa',
        'is_active' => true,
    ]);

    expect($tenant->getId())->toBeInt();
    expect($tenant->getName())->toBe('Interface Test Store');
    expect($tenant->getSlug())->toBe('interface-test-store');
    expect($tenant->getDomain())->toBe('test.satora.test');
    expect($tenant->getTheme())->toBe('minimal-luxury');
    expect($tenant->getTemplate())->toBe('fashion');
    expect($tenant->getLocale())->toBe('fa');
    expect($tenant->isActive())->toBeTrue();
});

test('tenant customer_panel_features is cast to array', function () {
    $tenant = Tenant::create([
        'name' => 'Test Store',
        'slug' => 'features-test',
        'customer_panel_features' => ['orders', 'wallet', 'wishlist'],
    ]);

    $fresh = Tenant::find($tenant->id);

    expect($fresh->customer_panel_features)->toBeArray();
    expect($fresh->customer_panel_features)->toContain('wishlist');
});
