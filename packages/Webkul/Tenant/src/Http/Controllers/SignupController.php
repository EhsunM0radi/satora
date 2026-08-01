<?php

namespace Webkul\Tenant\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Webkul\BusinessPreset\Helpers\PresetApplier;
use Webkul\BusinessPreset\Helpers\PresetRegistry;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:tenants,slug|alpha_dash',
            'business_type' => 'nullable|string',
            'theme' => 'nullable|string',
            'template' => 'nullable|string',
            'locale' => 'nullable|string|in:fa,en,ar,tr',
            'admin_email' => 'required|email',
            'admin_password' => 'required|string|min:8',
        ]);

        DB::beginTransaction();

        try {
            $tenant = $this->tenantRepository->create([
                'name' => $validated['store_name'],
                'slug' => $validated['slug'],
                'business_type' => $validated['business_type'] ?? null,
                'theme' => $validated['theme'] ?? 'minimal-luxury',
                'template' => $validated['template'] ?? 'fashion',
                'locale' => $validated['locale'] ?? 'fa',
            ]);

            // Create admin user
            $admin = Admin::create([
                'name' => $validated['store_name'],
                'email' => $validated['admin_email'],
                'password' => Hash::make($validated['admin_password']),
                'status' => 1,
                'role_id' => 1,
            ]);

            $tenant->users()->attach($admin->id, ['role' => 'tenant_admin']);

            // Apply business preset
            if (! empty($validated['business_type'])) {
                try {
                    $registry = app(PresetRegistry::class);
                    $applier = app(PresetApplier::class);
                    $preset = $registry->get($validated['business_type']);
                    if ($preset) {
                        $applier->apply($preset);
                        $tenant->theme = $preset->getRecommendedTheme();
                        $tenant->template = $preset->getRecommendedTemplate();
                        $tenant->save();
                    }
                } catch (\Exception $e) {
                    report($e);
                }
            }

            DB::commit();

            return redirect()->route('admin.session.create')
                ->with('success', __('tenant::app.store_created'));
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
