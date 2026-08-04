<?php

use Illuminate\Support\Facades\DB;
use Webkul\Tenant\Models\Tenant;
use Webkul\User\Models\Admin;

// ── Tenant API Feature Tests ──

beforeEach(function () {
    // Seed a role for admin creation
    DB::table('roles')->insert([
        'id' => 1,
        'name' => 'Administrator',
        'description' => 'Administrator role',
        'permission_type' => 'all',
    ]);
});

// ── POST /api/v1/tenant ──

test('POST /api/v1/tenant creates tenant and attaches admin user', function () {
    $admin = Admin::create([
        'name' => 'API Admin',
        'email' => 'api-admin@test.com',
        'password' => bcrypt('password123'),
        'status' => 1,
        'role_id' => 1,
    ]);

    $response = $this->actingAs($admin, 'admin')
        ->postJson('/api/v1/tenant', [
            'name' => 'API Created Store',
            'slug' => 'api-store',
            'business_type' => 'fashion',
            'locale' => 'fa',
            'admin_email' => 'api-admin@test.com',
            'admin_name' => 'API Admin',
            'admin_password' => 'password123',
        ]);

    $response->assertStatus(201);
    $response->assertJsonPath('message', 'Tenant created successfully.');
    $response->assertJsonPath('tenant.name', 'API Created Store');
    $response->assertJsonPath('tenant.slug', 'api-store');

    // Verify tenant was created in DB
    $tenant = Tenant::where('slug', 'api-store')->first();
    expect($tenant)->not->toBeNull();
    expect($tenant->name)->toBe('API Created Store');

    // Verify admin was attached
    expect($tenant->users)->toHaveCount(1);
    expect($tenant->users->first()->email)->toBe('api-admin@test.com');
});

test('POST /api/v1/tenant creates with all fields', function () {
    $admin = Admin::create([
        'name' => 'Full Admin',
        'email' => 'full-admin@test.com',
        'password' => bcrypt('password123'),
        'status' => 1,
        'role_id' => 1,
    ]);

    $response = $this->actingAs($admin, 'admin')
        ->postJson('/api/v1/tenant', [
            'name' => 'Full Store',
            'slug' => 'full-store',
            'business_type' => 'electronics',
            'theme' => 'modern-dark',
            'template' => 'electronics',
            'locale' => 'en',
            'mobile' => '09121234567',
            'address' => 'Tehran, Iran',
            'domain' => 'full.satora.test',
            'modules' => ['inventory', 'crm'],
            'settings' => ['currency' => 'IRT', 'language' => 'fa'],
            'customer_panel_features' => ['orders', 'wallet'],
            'admin_email' => 'full-admin@test.com',
        ]);

    $response->assertStatus(201);
    $response->assertJsonPath('tenant.name', 'Full Store');

    $tenant = Tenant::where('slug', 'full-store')->first();
    expect($tenant->business_type)->toBe('electronics');
    expect($tenant->theme)->toBe('modern-dark');
    expect($tenant->template)->toBe('electronics');
    expect($tenant->locale)->toBe('en');
    expect($tenant->mobile)->toBe('09121234567');
    expect($tenant->address)->toBe('Tehran, Iran');
    expect($tenant->domain)->toBe('full.satora.test');
    expect($tenant->modules)->toBeArray();
    expect($tenant->modules)->toContain('inventory');
    expect($tenant->settings)->toBeArray();
    expect($tenant->settings['currency'])->toBe('IRT');
});

test('POST /api/v1/tenant validates required fields', function () {
    $admin = Admin::create([
        'name' => 'Validate Admin',
        'email' => 'validate@test.com',
        'password' => bcrypt('password123'),
        'status' => 1,
        'role_id' => 1,
    ]);

    $response = $this->actingAs($admin, 'admin')
        ->postJson('/api/v1/tenant', [
            // Missing name and slug
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name', 'slug']);
});

test('POST /api/v1/tenant enforces unique slug', function () {
    Tenant::create([
        'name' => 'Existing Store',
        'slug' => 'duplicate-slug',
    ]);

    $admin = Admin::create([
        'name' => 'Dup Admin',
        'email' => 'dup-admin@test.com',
        'password' => bcrypt('password123'),
        'status' => 1,
        'role_id' => 1,
    ]);

    $response = $this->actingAs($admin, 'admin')
        ->postJson('/api/v1/tenant', [
            'name' => 'Another Store',
            'slug' => 'duplicate-slug',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['slug']);
});

test('POST /api/v1/tenant returns 401 when not authenticated', function () {
    $response = $this->postJson('/api/v1/tenant', [
        'name' => 'Unauth Store',
        'slug' => 'unauth-store',
    ]);

    $response->assertStatus(401);
});

test('POST /api/v1/tenant stores modules as JSON', function () {
    $admin = Admin::create([
        'name' => 'Modules Admin',
        'email' => 'modules-admin@test.com',
        'password' => bcrypt('password123'),
        'status' => 1,
        'role_id' => 1,
    ]);

    $response = $this->actingAs($admin, 'admin')
        ->postJson('/api/v1/tenant', [
            'name' => 'Modules Store',
            'slug' => 'modules-store',
            'modules' => ['blog', 'forum', 'marketplace'],
        ]);

    $response->assertStatus(201);

    $tenant = Tenant::where('slug', 'modules-store')->first();
    expect($tenant->modules)->toBeArray();
    expect($tenant->modules)->toHaveCount(3);
    expect($tenant->modules)->toContain('blog');
    expect($tenant->modules)->toContain('forum');
    expect($tenant->modules)->toContain('marketplace');
});

test('POST /api/v1/tenant stores settings as JSON', function () {
    $admin = Admin::create([
        'name' => 'Settings Admin',
        'email' => 'settings-admin@test.com',
        'password' => bcrypt('password123'),
        'status' => 1,
        'role_id' => 1,
    ]);

    $response = $this->actingAs($admin, 'admin')
        ->postJson('/api/v1/tenant', [
            'name' => 'Settings Store',
            'slug' => 'settings-store',
            'settings' => [
                'default_currency' => 'IRT',
                'timezone' => 'Asia/Tehran',
                'tax_enabled' => true,
            ],
        ]);

    $response->assertStatus(201);

    $tenant = Tenant::where('slug', 'settings-store')->first();
    expect($tenant->settings)->toBeArray();
    expect($tenant->settings['default_currency'])->toBe('IRT');
    expect($tenant->settings['timezone'])->toBe('Asia/Tehran');
    expect($tenant->settings['tax_enabled'])->toBeTrue();
});

test('POST /api/v1/tenant validates locale enum', function () {
    $admin = Admin::create([
        'name' => 'Locale Admin',
        'email' => 'locale-admin@test.com',
        'password' => bcrypt('password123'),
        'status' => 1,
        'role_id' => 1,
    ]);

    $response = $this->actingAs($admin, 'admin')
        ->postJson('/api/v1/tenant', [
            'name' => 'Bad Locale Store',
            'slug' => 'bad-locale',
            'locale' => 'invalid-locale',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['locale']);
});

test('POST /api/v1/tenant creates admin if not exists', function () {
    // Don't pre-create admin — the controller should create one
    $admin = Admin::create([
        'name' => 'New Admin',
        'email' => 'new-admin@test.com',
        'password' => bcrypt('password123'),
        'status' => 1,
        'role_id' => 1,
    ]);

    $response = $this->actingAs($admin, 'admin')
        ->postJson('/api/v1/tenant', [
            'name' => 'New Admin Store',
            'slug' => 'new-admin-store',
            'admin_email' => 'brand-new@test.com',
            'admin_name' => 'Brand New',
            'admin_password' => 'password123',
        ]);

    $response->assertStatus(201);

    // Admin should be created
    $newAdmin = Admin::where('email', 'brand-new@test.com')->first();
    expect($newAdmin)->not->toBeNull();

    // And attached to the tenant
    $tenant = Tenant::where('slug', 'new-admin-store')->first();
    expect($tenant->users->pluck('id'))->toContain($newAdmin->id);
});
