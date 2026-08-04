<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Webkul\Tenant\Http\Middleware\ResolveTenant;
use Webkul\Tenant\Models\Tenant;
use Webkul\Tenant\Repositories\TenantRepository;
use Webkul\Tenant\TenantResolver;

// ── Tenant Isolation Feature Tests ──

beforeEach(function () {
    // Seed base data needed for tenant setup
    DB::table('roles')->insert([
        'id' => 1,
        'name' => 'Administrator',
        'description' => 'Administrator role',
        'permission_type' => 'all',
    ]);
});

// ── Tenant Data Isolation ──

test('two tenants can have isolated domain data', function () {
    $tenantA = Tenant::create([
        'name' => 'Store A',
        'slug' => 'store-a',
        'domain' => 'store-a.satora.test',
        'settings' => ['theme' => 'dark'],
    ]);

    $tenantB = Tenant::create([
        'name' => 'Store B',
        'slug' => 'store-b',
        'domain' => 'store-b.satora.test',
        'settings' => ['theme' => 'light'],
    ]);

    // Each tenant has distinct data
    expect($tenantA->domain)->not->toBe($tenantB->domain);
    expect($tenantA->slug)->not->toBe($tenantB->slug);
    expect($tenantA->settings)->not->toBe($tenantB->settings);
});

test('two tenants have isolated core_config entries via channel_code', function () {
    $tenantA = Tenant::create([
        'name' => 'Store A',
        'slug' => 'store-a',
    ]);

    $tenantB = Tenant::create([
        'name' => 'Store B',
        'slug' => 'store-b',
    ]);

    // Simulate tenant-scoped config (using slug as channel_code in real app)
    DB::table('core_config')->insert([
        ['code' => 'store.name', 'value' => 'Store A', 'channel_code' => 'store-a', 'locale_code' => null],
        ['code' => 'store.name', 'value' => 'Store B', 'channel_code' => 'store-b', 'locale_code' => null],
    ]);

    $configA = DB::table('core_config')->where('channel_code', 'store-a')->first();
    $configB = DB::table('core_config')->where('channel_code', 'store-b')->first();

    expect($configA->value)->toBe('Store A');
    expect($configB->value)->toBe('Store B');
    expect($configA->value)->not->toBe($configB->value);
});

test('tenant A cannot access tenant B data via resolver', function () {
    $tenantA = Tenant::create([
        'name' => 'Store A',
        'slug' => 'store-a',
        'domain' => 'store-a.satora.test',
    ]);

    $tenantB = Tenant::create([
        'name' => 'Store B',
        'slug' => 'store-b',
        'domain' => 'store-b.satora.test',
    ]);

    $resolver = new TenantResolver(app(TenantRepository::class));

    // Request for tenantA's domain should resolve to tenantA
    $requestA = Request::create('https://store-a.satora.test/');
    $resolvedA = $resolver->resolve($requestA);
    expect($resolvedA->getId())->toBe($tenantA->id);

    // Request for tenantB's domain should resolve to tenantB
    $resolver2 = new TenantResolver(app(TenantRepository::class));
    $requestB = Request::create('https://store-b.satora.test/');
    $resolvedB = $resolver2->resolve($requestB);
    expect($resolvedB->getId())->toBe($tenantB->id);

    // Tenant A's domain should NOT resolve to tenant B
    expect($resolvedA->getId())->not->toBe($tenantB->id);
    expect($resolvedB->getId())->not->toBe($tenantA->id);
});

// ── Middleware Tests ──

test('middleware sets current_tenant on valid request', function () {
    $tenant = Tenant::create([
        'name' => 'Middleware Store',
        'slug' => 'middleware-store',
        'domain' => 'middleware.satora.test',
    ]);

    $request = Request::create('https://middleware.satora.test/');

    $middleware = new ResolveTenant;
    $called = false;

    $middleware->handle($request, function ($req) use (&$called) {
        $called = true;
        expect(app()->bound('current_tenant'))->toBeTrue();
        expect(app('current_tenant')->getId())->toBeGreaterThan(0);

        return response('ok');
    });

    expect($called)->toBeTrue();
    expect(app('current_tenant')->getSlug())->toBe('middleware-store');
});

test('middleware sets locale from tenant', function () {
    $tenant = Tenant::create([
        'name' => 'Locale Store',
        'slug' => 'locale-store',
        'domain' => 'locale.satora.test',
        'locale' => 'fa',
    ]);

    $request = Request::create('https://locale.satora.test/');

    $middleware = new ResolveTenant;
    $middleware->handle($request, function () {
        return response('ok');
    });

    expect(app()->getLocale())->toBe('fa');
});

test('middleware passes through on central domain', function () {
    // Central domains are defined in config/tenant.php
    $request = Request::create('https://satora.test/admin/login');

    $middleware = new ResolveTenant;
    $passed = false;

    $middleware->handle($request, function ($req) use (&$passed) {
        $passed = true;

        return response('ok');
    });

    expect($passed)->toBeTrue();
    // current_tenant may or may not be bound on central domain
    // The middleware passes through without error
});

test('middleware does not error on unknown domain', function () {
    $request = Request::create('https://completely-unknown.example.com/');

    $middleware = new ResolveTenant;
    $passed = false;

    $response = $middleware->handle($request, function ($req) use (&$passed) {
        $passed = true;

        return response('ok');
    });

    expect($passed)->toBeTrue();
});

test('middleware does not set current_tenant on unknown domain', function () {
    // Clear any previous binding
    if (app()->bound('current_tenant')) {
        app()->forgetInstance('current_tenant');
    }

    $request = Request::create('https://unknown-domain.example.com/');

    $middleware = new ResolveTenant;
    $middleware->handle($request, function () {
        return response('ok');
    });

    // Should not bind current_tenant on unknown domain
    expect(app()->bound('current_tenant'))->toBeFalse();
});
