<?php

use Illuminate\Support\Facades\DB;
use Webkul\POS\Exceptions\PosSessionException;
use Webkul\POS\Models\PosCashMovement;
use Webkul\POS\Models\PosCashRegister;
use Webkul\POS\Models\PosLocation;
use Webkul\POS\Models\PosSession;
use Webkul\POS\Models\PosTerminal;
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
        'name' => 'POS Test', 'slug' => 'test-pos-session', 'locale' => 'en',
        'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $this->tenant = Tenant::find($tenantId);
    app()->instance('current_tenant', $this->tenant);

    $adminId = DB::table('admins')->insertGetId([
        'name' => 'Test Cashier', 'email' => 'session@pos.test', 'password' => bcrypt('password'),
        'role_id' => 1, 'status' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);
    auth('admin')->loginUsingId($adminId);

    $this->location = PosLocation::create([
        'name' => 'Main Store', 'code' => 'SES-MAIN', 'type' => 'store', 'tenant_id' => $this->tenant->id,
    ]);

    $this->terminal = PosTerminal::create([
        'name' => 'Register 1', 'code' => 'SES-REG1', 'tenant_id' => $this->tenant->id,
        'pos_location_id' => $this->location->id,
    ]);

    $this->sessionService = app(PosSessionService::class);
});

test('can open a POS session with opening balance', function () {
    $session = $this->sessionService->openSession($this->terminal, 500000, 'Morning shift');

    expect($session)->toBeInstanceOf(PosSession::class);
    expect($session->status)->toBe('open');
    expect((float) $session->opening_balance)->toBe(500000.0);
    expect($session->session_number)->toStartWith('POS-SES-REG1-');

    $register = PosCashRegister::where('pos_terminal_id', $this->terminal->id)->first();
    expect($register)->not->toBeNull();
    expect((float) $register->current_balance)->toBe(500000.0);

    $movement = PosCashMovement::where('pos_session_id', $session->id)
        ->where('type', 'opening')
        ->first();
    expect($movement)->not->toBeNull();
    expect((float) $movement->amount)->toBe(500000.0);
});

test('cannot open two sessions on same terminal', function () {
    $this->sessionService->openSession($this->terminal, 500000);

    $this->sessionService->openSession($this->terminal, 200000);
})->throws(PosSessionException::class);

test('can close a POS session', function () {
    $session = $this->sessionService->openSession($this->terminal, 500000);
    $closed = $this->sessionService->closeSession($session, 750000);

    expect($closed->status)->toBe('closed');
    expect((float) $closed->closing_balance)->toBe(750000.0);
    expect((float) $closed->expected_balance)->toBe(500000.0);
    expect((float) $closed->difference)->toBe(250000.0);
    expect($closed->closed_at)->not->toBeNull();
});

test('closing balance difference is calculated correctly', function () {
    $session = $this->sessionService->openSession($this->terminal, 100000);
    $closed = $this->sessionService->closeSession($session, 80000);

    expect((float) $closed->difference)->toBe(-20000.0);
});

test('cannot close an already closed session', function () {
    $session = $this->sessionService->openSession($this->terminal, 100000);
    $this->sessionService->closeSession($session, 100000);

    $this->sessionService->closeSession($session, 100000);
})->throws(PosSessionException::class);

test('session has correct relationships', function () {
    $session = $this->sessionService->openSession($this->terminal, 500000);

    expect($session->terminal->id)->toBe($this->terminal->id);
    expect($session->cashRegisters)->toHaveCount(1);
});

test('session numbers are sequential and unique', function () {
    $s1 = $this->sessionService->openSession($this->terminal, 100000);
    $this->sessionService->closeSession($s1, 100000);

    $s2 = $this->sessionService->openSession($this->terminal, 200000);

    expect($s2->session_number)->not->toBe($s1->session_number);
});
