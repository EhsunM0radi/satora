<?php

use Illuminate\Support\Facades\DB;
use Webkul\POS\Exceptions\PosPaymentException;
use Webkul\POS\Models\PosLocation;
use Webkul\POS\Models\PosPayment;
use Webkul\POS\Models\PosTerminal;
use Webkul\POS\Services\PosCheckoutService;
use Webkul\POS\Services\PosPaymentService;
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
        'name' => 'Pay Test', 'slug' => 'test-pay', 'locale' => 'en',
        'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $this->tenant = Tenant::find($tenantId);
    app()->instance('current_tenant', $this->tenant);

    $adminId = DB::table('admins')->insertGetId([
        'name' => 'Cashier', 'email' => 'pay@'.uniqid().'.test', 'password' => bcrypt('password'),
        'role_id' => 1, 'status' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);
    auth('admin')->loginUsingId($adminId);

    $location = PosLocation::create(['name' => 'Store', 'code' => 'PAY-MAIN', 'type' => 'store', 'tenant_id' => $this->tenant->id]);
    $this->terminal = PosTerminal::create([
        'name' => 'Reg', 'code' => 'PAY-R1', 'tenant_id' => $this->tenant->id, 'pos_location_id' => $location->id,
    ]);

    $this->session = app(PosSessionService::class)->openSession($this->terminal, 500000);
    $this->checkoutService = app(PosCheckoutService::class);
    $this->paymentService = app(PosPaymentService::class);
});

test('can process cash payment for an order', function () {
    $order = $this->checkoutService->createOrder($this->session, [
        ['product_id' => 1, 'name' => 'Product', 'quantity' => 1, 'price' => 250000],
    ]);

    $payment = $this->paymentService->processPayment($order, 'cash', 250000);

    expect($payment)->toBeInstanceOf(PosPayment::class);
    expect($payment->status)->toBe('approved');
    expect($payment->payment_method_code)->toBe('cash');
    expect((float) $payment->amount)->toBe(250000.0);
    expect($payment->reference_number)->toStartWith('MANUAL');

    $order->refresh();
    expect((float) $order->paid_amount)->toBe(250000.0);
    expect((float) $order->due_amount)->toBe(0.0);
});

test('can process split payment (cash + card)', function () {
    $order = $this->checkoutService->createOrder($this->session, [
        ['product_id' => 1, 'name' => 'Big Item', 'quantity' => 1, 'price' => 1000000],
    ]);

    $payments = $this->paymentService->processSplitPayment($order, [
        ['method' => 'cash', 'amount' => 500000, 'extra' => []],
        ['method' => 'card', 'amount' => 500000, 'extra' => ['card_last_four' => '1234']],
    ]);

    expect($payments)->toHaveCount(2);
    expect($payments[0]->payment_method_code)->toBe('cash');
    expect($payments[1]->payment_method_code)->toBe('card');

    $order->refresh();
    expect((float) $order->paid_amount)->toBe(1000000.0);
    expect((float) $order->due_amount)->toBe(0.0);
    expect($order->status)->toBe('completed');
});

test('cash payment updates register balance', function () {
    $order = $this->checkoutService->createOrder($this->session, [
        ['product_id' => 1, 'name' => 'Product', 'quantity' => 1, 'price' => 100000],
    ]);

    $register = $this->session->cashRegisters()->first();

    $this->paymentService->processPayment($order, 'cash', 100000, [
        'cash_register_id' => $register->id,
    ]);

    $register->refresh();
    expect((float) $register->current_balance)->toBe(600000.0);
});

test('card payment provides transaction reference', function () {
    $order = $this->checkoutService->createOrder($this->session, [
        ['product_id' => 1, 'name' => 'Card Item', 'quantity' => 1, 'price' => 300000],
    ]);

    $payment = $this->paymentService->processPayment($order, 'card', 300000, [
        'card_last_four' => '5678',
        'card_type' => 'visa',
    ]);

    expect($payment->reference_number)->toBe('5678');
    expect($payment->gateway_response['auth_code'])->not->toBeNull();
});

test('available payment methods includes all configured providers', function () {
    $methods = $this->paymentService->getAvailableMethods();

    $codes = collect($methods)->pluck('code')->toArray();
    expect($codes)->toContain('cash');
    expect($codes)->toContain('card');
    expect($codes)->toContain('wallet');
    expect($codes)->toContain('gift_card');
});

test('payment fails gracefully for unknown provider', function () {
    $order = $this->checkoutService->createOrder($this->session, [
        ['product_id' => 1, 'name' => 'X', 'quantity' => 1, 'price' => 1],
    ]);

    $this->paymentService->processPayment($order, 'bitcoin', 1);
})->throws(PosPaymentException::class);

test('insufficient payment throws exception', function () {
    $order = $this->checkoutService->createOrder($this->session, [
        ['product_id' => 1, 'name' => 'Expensive', 'quantity' => 1, 'price' => 1000000],
    ]);

    $this->paymentService->processSplitPayment($order, [
        ['method' => 'cash', 'amount' => 100000, 'extra' => []],
    ]);
})->throws(PosPaymentException::class);
