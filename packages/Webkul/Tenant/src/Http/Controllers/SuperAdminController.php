<?php

namespace Webkul\Tenant\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Webkul\Tenant\Repositories\TenantRepository;
use Webkul\User\Models\Admin;

class SuperAdminController extends Controller
{
    public function __construct(
        protected TenantRepository $tenantRepository
    ) {}

    /**
     * Dashboard — list all tenants.
     */
    public function index(): View
    {
        $tenants = $this->tenantRepository->all();

        return view('super_admin::dashboard', [
            'tenants' => $tenants,
        ]);
    }

    /**
     * Impersonate a tenant admin — login as their first admin and redirect.
     */
    public function impersonate(int $tenantId): RedirectResponse
    {
        $tenant = $this->tenantRepository->find($tenantId);
        if (! $tenant) {
            return back()->withErrors(['error' => 'تننت یافت نشد.']);
        }

        $admin = $tenant->users()->first();
        if (! $admin) {
            return back()->withErrors(['error' => 'این تننت ادمین ندارد.']);
        }

        // Log out current super admin
        Auth::guard('admin')->logout();

        // Login as tenant admin
        Auth::guard('admin')->login($admin);

        // Set tenant context
        app()->instance('current_tenant', $tenant);

        return redirect()->route('admin.dashboard.index')
            ->with('success', "شما وارد پنل «{$tenant->getName()}» شدید.");
    }

    /**
     * Create a new tenant (admin form).
     */
    public function create(): View
    {
        return view('super_admin::create');
    }

    /**
     * Store a new tenant.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:tenants,slug|alpha_dash',
            'domain' => 'nullable|string|unique:tenants,domain',
            'business_type' => 'nullable|string',
            'locale' => 'nullable|string|in:fa,en,ar,tr',
            'is_active' => 'boolean',
        ]);

        $this->tenantRepository->create($validated);

        return redirect()->route('super_admin.dashboard')
            ->with('success', 'تننت با موفقیت ساخته شد.');
    }
}
