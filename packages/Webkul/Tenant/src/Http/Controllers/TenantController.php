<?php

namespace Webkul\Tenant\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Webkul\BusinessPreset\Helpers\PresetApplier;
use Webkul\BusinessPreset\Helpers\PresetRegistry;
use Webkul\Tenant\Repositories\TenantRepository;
use Webkul\User\Models\Admin;

class TenantController extends Controller
{
    public function __construct(
        protected TenantRepository $tenantRepository
    ) {}

    /**
     * Create a new tenant store.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:tenants,slug',
            'business_type' => 'nullable|string',
            'theme' => 'nullable|string',
            'template' => 'nullable|string',
            'locale' => 'nullable|string|in:fa,en,ar,tr',
            'mobile' => 'nullable|string',
            'address' => 'nullable|string',
            'domain' => 'nullable|string',
            'modules' => 'nullable|array',
            'settings' => 'nullable|array',
            'customer_panel_features' => 'nullable|array',
            // Admin user credentials
            'admin_name' => 'nullable|string',
            'admin_email' => 'nullable|email',
            'admin_password' => 'nullable|string|min:8',
        ]);

        DB::beginTransaction();

        try {
            $tenant = $this->tenantRepository->create($validated);

            // Create or attach admin user
            if ($request->filled('admin_email')) {
                $admin = Admin::firstOrCreate(
                    ['email' => $request->admin_email],
                    [
                        'name' => $request->admin_name ?? $request->name,
                        'password' => Hash::make($request->admin_password ?? 'password'),
                        'status' => 1,
                        'role_id' => 1,
                    ]
                );

                $tenant->users()->attach($admin->id, ['role' => 'tenant_admin']);
            }

            // Apply business preset if specified
            if ($request->filled('business_type')) {
                $this->applyPreset($tenant, $request->business_type);
                $tenant->refresh();
            }

            DB::commit();

            return response()->json([
                'message' => 'Tenant created successfully.',
                'tenant' => [
                    'id' => $tenant->getId(),
                    'name' => $tenant->getName(),
                    'slug' => $tenant->getSlug(),
                    'domain' => $tenant->getDomain(),
                    'theme' => $tenant->getTheme(),
                    'template' => $tenant->getTemplate(),
                    'locale' => $tenant->getLocale(),
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function applyPreset($tenant, string $businessType): void
    {
        try {
            $registry = app(PresetRegistry::class);
            $applier = app(PresetApplier::class);

            $preset = $registry->get($businessType);
            if ($preset) {
                $applier->apply($preset);

                // Update tenant with preset's theme/template
                $tenant->theme = $preset->getRecommendedTheme();
                $tenant->template = $preset->getRecommendedTemplate();
                $tenant->save();
            }
        } catch (\Exception $e) {
            report($e);
        }
    }
}
