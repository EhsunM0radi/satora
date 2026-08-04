<?php

use Illuminate\Support\Facades\DB;
use Webkul\POS\Models\PosLocation;
use Webkul\POS\Models\PosOrder;
use Webkul\POS\Models\PosTerminal;
use Webkul\POS\Services\PosCheckoutService;
use Webkul\POS\Services\PosSessionService;
use Webkul\Tenant\Models\Tenant;

beforeEach(function () {
    if (! DB::table('roles')->where('id', 1)->exists()) {
        DB::table('roles')->insert([
            'id' => 1, 'name' => 'Administrator', 'description' => 'Admin',
            'permission_type' => 'all', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    $tenantId = DB::table('tenants')->insertGetId([
        'name' => 'Test CO', 'slug' => 'test-co', 'locale' => 'en',
        'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $this->tenant = Tenant::find($tenantId);
    app()->instance('current_tenant', $this->tenant);

    $adminId = DB::table('admins')->insertGetId([
        'name' => 'Cashier', 'email' => 'co@'.uniqid().'.test', 'password' => bcrypt('password'),
        'role_id' => 1, 'status' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);
    auth('admin')->loginUsingId($adminId);

    $location = PosLocation::create(['name' => 'Store', 'code' => 'CO-MAIN', 'type' => 'store', 'tenant_id' => $this->tenant->id]);
    $this->terminal = PosTerminal::create([
        'name' => 'Reg', 'code' => 'CO-R1', 'tenant_id' => $this->tenant->id, 'pos_location_id' => $location->id,
    ]);

    $this->session = app(PosSessionService::class)->openSession($this->terminal, 500000);
    $this->checkoutService = app(PosCheckoutService::class);
});

test('can create a POS order with items', function () {
    $order = $this->checkoutService->createOrder($this->session, [
        ['product_id' => 1, 'name' => 'Product A', 'quantity' => 2, 'price' => 250000],
        ['product_id' => 2, 'name' => 'Product B', 'quantity' => 1, 'price' => 150000],
    ]);

    expect($order)->toBeInstanceOf(PosOrder::class);
    expect($order->status)->toBe('draft');
    expect($order->items)->toHaveCount(2);
    expect((float) $order->due_amount)->toBe((float) $order->total);
    expect($order->order_number)->toStartWith('POS-CO-R1-');
});

test('order items have correct calculations', function () {
    $order = $this->checkoutService->createOrder($this->session, [
        ['product_id' => 1, 'name' => 'Widget', 'quantity' => 3, 'price' => 100000],
    ]);

    $item = $order->items->first();
    expect((float) $item->quantity)->toBe(3.0);
    expect((float) $item->unit_price)->toBe(100000.0);
    expect((float) $item->total)->toBe(300000.0);
});

test('can add items to existing order', function () {
    $order = $this->checkoutService->createOrder($this->session, [
        ['product_id' => 1, 'name' => 'Item A', 'quantity' => 1, 'price' => 100000],
    ]);

    $this->checkoutService->addItem($order, [
        'product_id' => 2, 'name' => 'Item B', 'quantity' => 2, 'price' => 50000,
    ]);

    $order->refresh();
    expect($order->items)->toHaveCount(2);
});

test('can update item quantity', function () {
    $order = $this->checkoutService->createOrder($this->session, [
        ['product_id' => 1, 'name' => 'Item', 'quantity' => 1, 'price' => 100000],
    ]);

    $item = $order->items->first();
    $this->checkoutService->updateItemQuantity($order, $item, 5);

    $item->refresh();
    expect((float) $item->quantity)->toBe(5.0);
    expect((float) $item->total)->toBe(500000.0);
});

test('can remove items from order', function () {
    $order = $this->checkoutService->createOrder($this->session, [
        ['product_id' => 1, 'name' => 'A', 'quantity' => 1, 'price' => 100000],
        ['product_id' => 2, 'name' => 'B', 'quantity' => 1, 'price' => 200000],
    ]);

    $item = $order->items->first();
    $this->checkoutService->removeItem($order, $item);

    expect($order->fresh()->items)->toHaveCount(1);
});

test('can hold and resume orders', function () {
    $order = $this->checkoutService->createOrder($this->session, [
        ['product_id' => 1, 'name' => 'Hold Item', 'quantity' => 1, 'price' => 50000],
    ]);

    $this->checkoutService->holdOrder($order);
    expect($order->fresh()->status)->toBe('held');
    expect($order->fresh()->held_at)->not->toBeNull();

    $this->checkoutService->resumeOrder($order);
    expect($order->fresh()->status)->toBe('draft');
    expect($order->fresh()->held_at)->toBeNull();
});

test('can void an order with reason', function () {
    $order = $this->checkoutService->createOrder($this->session, [
        ['product_id' => 1, 'name' => 'Void Item', 'quantity' => 1, 'price' => 50000],
    ]);

    $this->checkoutService->voidOrder($order, 'Customer changed mind');

    expect($order->fresh()->status)->toBe('voided');
    expect($order->fresh()->voided_at)->not->toBeNull();
    expect($order->fresh()->notes)->toContain('Customer changed mind');
});

test('can complete an order', function () {
    $order = $this->checkoutService->createOrder($this->session, [
        ['product_id' => 1, 'name' => 'Done', 'quantity' => 1, 'price' => 50000],
    ]);

    $this->checkoutService->completeOrder($order);

    expect($order->fresh()->status)->toBe('completed');
    expect($order->fresh()->completed_at)->not->toBeNull();
    expect((float) $order->fresh()->due_amount)->toBe(0.0);
});

test('order numbers are sequential', function () {
    $o1 = $this->checkoutService->createOrder($this->session, [
        ['product_id' => 1, 'name' => 'A', 'quantity' => 1, 'price' => 1],
    ]);
    $o2 = $this->checkoutService->createOrder($this->session, [
        ['product_id' => 1, 'name' => 'B', 'quantity' => 1, 'price' => 1],
    ]);

    expect($o2->order_number)->not->toBe($o1->order_number);
});
