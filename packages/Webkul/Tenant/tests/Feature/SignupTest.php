<?php

use Illuminate\Support\Facades\DB;
use Webkul\Tenant\Models\Tenant;
use Webkul\User\Models\Admin;

// ── Signup Feature Tests ──

beforeEach(function () {
    DB::table('roles')->insert([
        'id' => 1,
        'name' => 'Administrator',
        'description' => 'Administrator role',
        'permission_type' => 'all',
    ]);
});

// ── GET /signup ──

test('GET /signup returns 200', function () {
    $response = $this->get('/signup');

    $response->assertStatus(200);
});

test('GET /signup returns signup view', function () {
    $response = $this->get('/signup');

    $response->assertViewIs('tenant::signup');
});

// ── POST /signup ──

test('POST /signup creates user and tenant', function () {
    $response = $this->post('/signup', [
        'name' => 'Signup User',
        'email' => 'signup@test.com',
        'password' => 'password123',
    ]);

    // Should redirect to onboarding after successful signup
    $response->assertRedirect(route('onboarding.show'));

    // Verify admin was created
    $admin = Admin::where('email', 'signup@test.com')->first();
    expect($admin)->not->toBeNull();
    expect($admin->name)->toBe('Signup User');

    // Verify tenant was created
    expect($admin->tenants)->toHaveCount(1);
    $tenant = $admin->tenants->first();
    expect($tenant)->not->toBeNull();
    expect($tenant->slug)->not->toBeEmpty();
});

test('POST /signup validates required fields', function () {
    $response = $this->post('/signup', []);

    $response->assertSessionHasErrors(['name', 'email', 'password']);
});

test('POST /signup validates email uniqueness', function () {
    // Create an admin first
    $this->post('/signup', [
        'name' => 'First User',
        'email' => 'duplicate@test.com',
        'password' => 'password123',
    ]);

    // Try to signup with the same email
    $response = $this->post('/signup', [
        'name' => 'Second User',
        'email' => 'duplicate@test.com',
        'password' => 'password456',
    ]);

    $response->assertSessionHasErrors(['email']);
});

test('POST /signup validates password minimum length', function () {
    $response = $this->post('/signup', [
        'name' => 'Short Pass User',
        'email' => 'shortpass@test.com',
        'password' => 'short',
    ]);

    $response->assertSessionHasErrors(['password']);
});

test('POST /signup auto-generates unique slug from email', function () {
    $response = $this->post('/signup', [
        'name' => 'Slug User',
        'email' => 'slug.user@test.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('onboarding.show'));

    $tenant = Tenant::latest()->first();
    // Slug should be derived from the email local part
    expect($tenant->slug)->toContain('sluguser');
});

test('POST /signup auto-logins the new admin', function () {
    $response = $this->post('/signup', [
        'name' => 'Login User',
        'email' => 'login@test.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('onboarding.show'));

    // Admin should be logged in
    expect(auth()->guard('admin')->check())->toBeTrue();
    expect(auth()->guard('admin')->user()->email)->toBe('login@test.com');
});

// ── OTP Signup Flow ──

test('GET /signup/otp/verify redirects without phone in session', function () {
    $response = $this->get('/signup/otp/verify');

    $response->assertRedirect(route('signup.show'));
});

test('POST /signup/otp/send stores phone in session, generates OTP, and redirects', function () {
    $response = $this->post('/signup/otp/send', [
        'phone' => '09121110001',
    ]);

    $response->assertRedirect(route('signup.otp.verify'));
    expect(session('signup_phone'))->toBe('09121110001');

    // Verify an OTP code was persisted in the database
    $code = DB::table('satora_otp_codes')
        ->where('phone', '09121110001')
        ->where('purpose', 'signup')
        ->whereNull('used_at')
        ->where('expires_at', '>', now())
        ->first();

    expect($code)->not->toBeNull();
    expect(strlen($code->code))->toBe(6);
});

test('POST /signup/otp/verify rejects invalid OTP', function () {
    session(['signup_phone' => '09121110002']);

    // Send a real OTP first so we can test rejection
    $this->post('/signup/otp/send', ['phone' => '09121110002']);

    $response = $this->post('/signup/otp/verify', [
        'otp' => '000000',
        'name' => 'OTP User',
    ]);

    $response->assertSessionHasErrors(['otp']);
});

test('POST /signup/otp/verify with correct OTP creates account', function () {
    $phone = '09121110003';
    session(['signup_phone' => $phone]);

    // Send OTP and extract the generated code from DB
    $this->post('/signup/otp/send', ['phone' => $phone]);

    $otpRecord = DB::table('satora_otp_codes')
        ->where('phone', $phone)
        ->where('purpose', 'signup')
        ->whereNull('used_at')
        ->where('expires_at', '>', now())
        ->first();

    expect($otpRecord)->not->toBeNull();

    // Verify with the actual generated code
    $response = $this->post('/signup/otp/verify', [
        'otp' => $otpRecord->code,
        'name' => 'OTP Phone User',
    ]);

    $response->assertRedirect(route('onboarding.show'));

    // Admin should be created with phone-based email
    $admin = Admin::where('email', $phone.'@otp.satora.local')->first();
    expect($admin)->not->toBeNull();
    expect($admin->name)->toBe('OTP Phone User');

    // Tenant should be created
    expect($admin->tenants)->toHaveCount(1);
});

test('POST /signup/otp/verify requires phone in session', function () {
    $response = $this->post('/signup/otp/verify', [
        'otp' => '111111',
        'name' => 'No Phone User',
    ]);

    $response->assertRedirect(route('signup.show'));
});
