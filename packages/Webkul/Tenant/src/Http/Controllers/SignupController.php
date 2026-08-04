<?php

namespace Webkul\Tenant\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Webkul\Tenant\Repositories\TenantRepository;
use Webkul\Tenant\Services\OtpService;
use Webkul\User\Models\Admin;

class SignupController extends Controller
{
    public function __construct(
        protected TenantRepository $tenantRepository,
        protected OtpService $otpService
    ) {}

    public function show(): View
    {
        return view('tenant::signup');
    }

    /**
     * Email + Password signup.
     *
     * Creates an admin user with a temporary tenant placeholder.
     * Business details (store name, slug, niche, theme) are completed
     * in the onboarding wizard that follows.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:8',
        ]);

        DB::beginTransaction();

        try {
            $admin = Admin::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => 1,
                'role_id' => 1,
            ]);

            $slug = $this->generateUniqueSlug($validated['email']);

            $tenant = $this->tenantRepository->create([
                'name' => __('tenant::onboarding.wizard.my_store_default', ['name' => $validated['name']]),
                'slug' => $slug,
                'locale' => 'fa',
            ]);

            $tenant->users()->attach($admin->id, ['role' => 'tenant_admin']);

            DB::commit();

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
     * Request an OTP verification code via SMS.
     *
     * Generates a random 6-digit code, persists it with a 5-minute expiry,
     * and sends it via the configured SMS driver.
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        $phone = $request->input('phone');

        // Rate-limit: max 5 OTP requests per phone in 15 minutes
        if ($this->otpService->recentAttempts($phone) >= 5) {
            return back()->withErrors([
                'phone' => __('tenant::signup.too_many_otp_requests'),
            ]);
        }

        $this->otpService->send($phone, 'signup');

        session()->put('signup_phone', $phone);

        return redirect()->route('signup.otp.verify')
            ->with('status', __('tenant::signup.otp_sent'));
    }

    /**
     * OTP verification page.
     */
    public function showOtpVerify(): RedirectResponse|View
    {
        if (! session()->has('signup_phone')) {
            return redirect()->route('signup.show');
        }

        return view('tenant::signup-otp', [
            'phone' => session('signup_phone'),
        ]);
    }

    /**
     * Verify the OTP code and create the account.
     *
     * Uses an atomic update to prevent replay — each code can
     * only be used once.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => 'required|string|size:6',
            'name' => 'required|string|max:255',
        ]);

        $phone = session('signup_phone');
        if (! $phone) {
            return redirect()->route('signup.show');
        }

        $code = $request->input('otp');

        // Verify the code atomically
        if (! $this->otpService->verify($phone, $code, 'signup')) {
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
                'mobile' => $phone,
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
        $local = strstr($input, '@', true);
        $base = $local
            ? Str::slug(preg_replace('/[^a-zA-Z0-9]/', '', $local) ?: 'store')
            : 'store-'.Str::lower(Str::random(6));

        $slug = $base;
        $counter = 1;

        while ($this->tenantRepository->findBySlug($slug)) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
