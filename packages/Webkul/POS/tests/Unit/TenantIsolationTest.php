<?php

use Illuminate\Support\Facades\DB;
use Webkul\POS\Models\PosLocation;
use Webkul\Tenant\Models\Tenant;

beforeEach(function () {
    if (! DB::table('roles')->where('id', 1)->exists()) {
        DB::table('roles')->insert([
            'id' => 1, 'name' => 'Administrator', 'description' => 'Admin',
            'permission_type' => 'all', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
});

function createTestTenant(string $name, string $slug): Tenant
{
    $id = DB::table('tenants')->insertGetId([
        'name' => $name, 'slug' => $slug, 'locale' => 'en',
        'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);

    return Tenant::find($id);
}

test('BelongsToTenant trait auto-sets tenant_id when context is available', function () {
    $tenant = createTestTenant('Test Store', 'test-store');
    app()->instance('current_tenant', $tenant);

    $location = PosLocation::create([
        'name' => 'Test Location',
        'code' => 'TL001',
        'type' => 'store',
    ]);

    expect($location->tenant_id)->toBe($tenant->id);
});

test('TenantIsolationScope filters queries by current tenant', function () {
    $tenantA = createTestTenant('Store A', 'store-a');
    $tenantB = createTestTenant('Store B', 'store-b');

    app()->instance('current_tenant', $tenantA);
    PosLocation::create(['name' => 'Location A1', 'code' => 'LA1', 'type' => 'store', 'tenant_id' => $tenantA->id]);
    PosLocation::create(['name' => 'Location A2', 'code' => 'LA2', 'type' => 'store', 'tenant_id' => $tenantA->id]);

    app()->instance('current_tenant', $tenantB);
    PosLocation::create(['name' => 'Location B1', 'code' => 'LB1', 'type' => 'store', 'tenant_id' => $tenantB->id]);

    app()->instance('current_tenant', $tenantA);
    $locationsA = PosLocation::all();
    expect($locationsA)->toHaveCount(2);

    app()->instance('current_tenant', $tenantB);
    $locationsB = PosLocation::all();
    expect($locationsB)->toHaveCount(1);
    expect($locationsB->first()->code)->toBe('LB1');
});

test('withoutTenantScope bypasses isolation for admin operations', function () {
    $tenantA = createTestTenant('Store A', 'store-a2');
    $tenantB = createTestTenant('Store B', 'store-b2');

    PosLocation::create(['name' => 'L1', 'code' => 'L1', 'type' => 'store', 'tenant_id' => $tenantA->id]);
    PosLocation::create(['name' => 'L2', 'code' => 'L2', 'type' => 'store', 'tenant_id' => $tenantB->id]);

    $all = PosLocation::withoutTenantScope()->get();
    expect($all)->toHaveCount(2);
});

test('pos_employee_roles are system-wide with null tenant_id', function () {
    $roles = DB::table('pos_employee_roles')->get();
    expect($roles)->toHaveCount(4);

    foreach ($roles as $role) {
        expect($role->tenant_id)->toBeNull();
        expect($role->is_system)->toBe(1);
    }
});

test('pos_employee_roles have correct permission matrices', function () {
    $owner = DB::table('pos_employee_roles')->where('code', 'owner')->first();
    $cashier = DB::table('pos_employee_roles')->where('code', 'cashier')->first();

    $ownerPerms = json_decode($owner->permissions, true);
    $cashierPerms = json_decode($cashier->permissions, true);

    expect($ownerPerms)->toHaveCount(12);
    foreach ($ownerPerms as $perm) {
        expect($perm)->toBeTrue();
    }

    expect($cashierPerms['pos.create_sale'])->toBeTrue();
    expect($cashierPerms['pos.process_refund'])->toBeFalse();
    expect($cashierPerms['pos.apply_discount'])->toBeFalse();
    expect($cashierPerms['pos.change_price'])->toBeFalse();
    expect($cashierPerms['pos.view_reports'])->toBeFalse();
    expect($cashierPerms['pos.open_drawer'])->toBeTrue();
    expect($cashierPerms['pos.access_customers'])->toBeTrue();
});
