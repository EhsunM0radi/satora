<?php

namespace Webkul\Tenant\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Webkul\Tenant\Repositories\TenantRepository;
use Webkul\User\Models\Admin;

class SignupController extends Controller
{
    public function __construct(
        protected TenantRepository $tenantRepository
    ) {}

    public function show(): View
    {
        return view('tenant::signup');
    }

    /**
     * Email + Password signup.
     * Creates user + placeholder tenant, then redirects to onboarding wizard.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:8',
        ]);

        DB::beginTransaction();

        try {
            // Create admin user
            $admin = Admin::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => 1,
                'role_id' => 1,
            ]);

            // Auto-generate a unique slug from email (user can change in wizard)
            $slug = $this->generateUniqueSlug($validated['email']);

            // Create placeholder tenant — business details filled in wizard
            $tenant = $this->tenantRepository->create([
                'name' => __('tenant::onboarding.wizard.my_store_default', ['name' => $validated['name']]),
                'slug' => $slug,
                'locale' => 'fa',
            ]);

            $tenant->users()->attach($admin->id, ['role' => 'tenant_admin']);

            DB::commit();

            // Auto-login the new admin
            Auth::guard('admin')->login($admin);

            session()->put('locale', 'fa');
            session()->put('admin_locale', 'fa');

            return redirect()->route('onboarding.show')
                ->with('success', __('tenant::app.store_created'));

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * OTP request — send verification code via SMS.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        // TODO: Implement OTP sending via SMS provider
        // For now, redirect to OTP verification page with phone in session
        session()->put('signup_phone', $request->input('phone'));

        return redirect()->route('signup.otp.verify');
    }

    /**
     * OTP verification page.
     */
    public function showOtpVerify(): View
    {
        if (! session()->has('signup_phone')) {
            return redirect()->route('signup.show');
        }

        return view('tenant::signup-otp', [
            'phone' => session('signup_phone'),
        ]);
    }

    /**
     * Verify OTP and create account.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
            'name' => 'required|string|max:255',
        ]);

        $phone = session('signup_phone');
        if (! $phone) {
            return redirect()->route('signup.show');
        }

        // Test OTP: always accept 111111; real SMS verification TBD
        if ($request->input('otp') !== '111111') {
            return back()->withErrors(['otp' => __('tenant::signup.invalid_otp')]);
        }

        DB::beginTransaction();

        try {
            $admin = Admin::create([
                'name' => $request->input('name'),
                'email' => $phone.'@otp.satora.local',
                'password' => Hash::make(Str::random(32)),
                'status' => 1,
                'role_id' => 1,
            ]);

            $slug = $this->generateUniqueSlug($phone);

            $tenant = $this->tenantRepository->create([
                'name' => __('tenant::onboarding.wizard.my_store_default', ['name' => $request->input('name')]),
                'slug' => $slug,
                'locale' => 'fa',
            ]);

            $tenant->users()->attach($admin->id, ['role' => 'tenant_admin']);

            DB::commit();

            Auth::guard('admin')->login($admin);

            session()->forget('signup_phone');
            session()->put('locale', 'fa');
            session()->put('admin_locale', 'fa');

            return redirect()->route('onboarding.show')
                ->with('success', __('tenant::app.store_created'));

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Generate a unique slug from an email or phone string.
     */
    protected function generateUniqueSlug(string $input): string
    {
        // Extract the local part of email or use the whole string
        $base = strstr($input, '@', true) ?: $input;
        $base = Str::slug(preg_replace('/[^a-zA-Z0-9]/', '', $base) ?: 'store');

        $slug = $base;
        $counter = 1;

        while ($this->tenantRepository->findBySlug($slug)) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
