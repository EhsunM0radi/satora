<?php

use Illuminate\Http\Request;
use Webkul\Tenant\Models\Tenant;
use Webkul\Tenant\Repositories\TenantRepository;
use Webkul\Tenant\TenantResolver;

// ── TenantResolver Tests ──

beforeEach(function () {
    $this->repository = app(TenantRepository::class);
    $this->resolver = new TenantResolver($this->repository);
});

test('resolves tenant by exact domain match', function () {
    $tenant = Tenant::create([
        'name' => 'Domain Store',
        'slug' => 'domain-store',
        'domain' => 'mystore.satora.com',
    ]);

    $request = Request::create('https://mystore.satora.com/');
    $resolved = $this->resolver->resolve($request);

    expect($resolved)->not->toBeNull();
    expect($resolved->getId())->toBe($tenant->id);
    expect($resolved->getSlug())->toBe('domain-store');
});

test('resolves tenant by subdomain in test environment', function () {
    $tenant = Tenant::create([
        'name' => 'Subdomain Store',
        'slug' => 'sub-store',
    ]);

    $request = Request::create('https://sub-store.satora.test/');
    $resolved = $this->resolver->resolve($request);

    expect($resolved)->not->toBeNull();
    expect($resolved->getId())->toBe($tenant->id);
});

test('resolves tenant by path in local environment', function () {
    app()['env'] = 'local';

    $tenant = Tenant::create([
        'name' => 'Path Store',
        'slug' => 'path-store',
    ]);

    $request = Request::create('https://satora.test/shop/path-store');
    $resolved = $this->resolver->resolve($request);

    expect($resolved)->not->toBeNull();
    expect($resolved->getId())->toBe($tenant->id);

    app()['env'] = 'testing';
});

test('resolves tenant by path with trailing slash', function () {
    app()['env'] = 'local';

    $tenant = Tenant::create([
        'name' => 'Trailing Store',
        'slug' => 'trailing-store',
    ]);

    $request = Request::create('https://satora.test/shop/trailing-store/');
    $resolved = $this->resolver->resolve($request);

    expect($resolved)->not->toBeNull();
    expect($resolved->getSlug())->toBe('trailing-store');

    app()['env'] = 'testing';
});

test('resolves tenant by path with sub-path', function () {
    app()['env'] = 'local';

    $tenant = Tenant::create([
        'name' => 'SubPath Store',
        'slug' => 'subpath-store',
    ]);

    $request = Request::create('https://satora.test/shop/subpath-store/admin/dashboard');
    $resolved = $this->resolver->resolve($request);

    expect($resolved)->not->toBeNull();
    expect($resolved->getSlug())->toBe('subpath-store');

    app()['env'] = 'testing';
});

test('returns null for unknown domain', function () {
    $request = Request::create('https://nonexistent.satora.test/');
    $resolved = $this->resolver->resolve($request);

    expect($resolved)->toBeNull();
});

test('returns null for unknown path in local environment', function () {
    app()['env'] = 'local';

    $request = Request::create('https://satora.test/shop/nonexistent');
    $resolved = $this->resolver->resolve($request);

    expect($resolved)->toBeNull();

    app()['env'] = 'testing';
});

test('does not resolve path-based tenant outside local environment', function () {
    $tenant = Tenant::create([
        'name' => 'Path Store',
        'slug' => 'no-path-store',
    ]);

    // In testing environment, path-based resolution is NOT available
    $request = Request::create('https://satora.test/shop/no-path-store');
    $resolved = $this->resolver->resolve($request);

    // Should not resolve via path since env is not 'local'
    expect($resolved)->toBeNull();
});

test('caches resolved tenant (returns same instance)', function () {
    $tenant = Tenant::create([
        'name' => 'Cache Test',
        'slug' => 'cache-test',
        'domain' => 'cache.satora.test',
    ]);

    $request = Request::create('https://cache.satora.test/');
    $first = $this->resolver->resolve($request);
    $second = $this->resolver->resolve($request);

    expect($first)->toBe($second);
    expect($first->getId())->toBe($tenant->id);
});

test('requireId throws exception when no tenant resolved', function () {
    $request = Request::create('https://unknown.test/');
    $this->resolver->resolve($request); // Will be null

    $this->resolver->requireId();
})->throws(RuntimeException::class, 'No tenant resolved.');

test('id returns null when no tenant resolved', function () {
    $request = Request::create('https://unknown.test/');
    $this->resolver->resolve($request);

    expect($this->resolver->id())->toBeNull();
});

test('id returns tenant id when resolved', function () {
    $tenant = Tenant::create([
        'name' => 'ID Test Store',
        'slug' => 'id-test',
        'domain' => 'id-test.satora.test',
    ]);

    $request = Request::create('https://id-test.satora.test/');
    $this->resolver->resolve($request);

    expect($this->resolver->id())->toBe($tenant->id);
});

test('does not resolve inactive tenant by domain', function () {
    $tenant = Tenant::create([
        'name' => 'Inactive Store',
        'slug' => 'inactive-resolve',
        'domain' => 'inactive.satora.test',
        'is_active' => false,
    ]);

    $request = Request::create('https://inactive.satora.test/');
    $resolved = $this->resolver->resolve($request);

    expect($resolved)->toBeNull();
});

test('does not resolve inactive tenant by subdomain', function () {
    $tenant = Tenant::create([
        'name' => 'Inactive Sub Store',
        'slug' => 'inactive-sub',
        'is_active' => false,
    ]);

    $request = Request::create('https://inactive-sub.satora.test/');
    $resolved = $this->resolver->resolve($request);

    expect($resolved)->toBeNull();
});
