<?php

namespace Webkul\Tenant\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Webkul\BusinessPreset\Helpers\PresetRegistry;
use Webkul\Tenant\Repositories\TenantRepository;

class OnboardingController extends Controller
{
    protected array $steps = ['business-info', 'preset', 'template', 'theme', 'complete'];

    public function __construct(
        protected TenantRepository $tenantRepository,
        protected PresetRegistry $presetRegistry
    ) {}

    /**
     * Show the onboarding wizard for the current tenant.
     */
    public function show(Request $request, ?string $step = null)
    {
        $tenant = $this->getCurrentTenant();
        if (! $tenant) {
            return redirect()->route('signup.show');
        }

        $currentStep = $step ?: 'business-info';
        $stepIndex = array_search($currentStep, $this->steps);

        return view('tenant::onboarding.wizard', [
            'tenant' => $tenant,
            'currentStep' => $currentStep,
            'stepIndex' => $stepIndex,
            'steps' => $this->steps,
            'presets' => $this->presetRegistry->all(),
        ]);
    }

    /**
     * Save the current step and advance.
     */
    public function store(Request $request)
    {
        $tenant = $this->getCurrentTenant();
        if (! $tenant) {
            return redirect()->route('signup.show');
        }

        $step = $request->input('step');
        $next = $request->input('next');

        switch ($step) {
            case 'business-info':
                $tenant->update($request->only(['mobile', 'address']));
                break;

            case 'preset':
                $presetCode = $request->input('preset_code');
                $tenant->business_type = $presetCode;
                $tenant->save();

                // Apply preset categories, pages, settings
                $this->applyPreset($tenant, $presetCode);
                break;

            case 'template':
                $tenant->template = $request->input('template');
                $tenant->save();
                break;

            case 'theme':
                $tenant->theme = $request->input('theme');
                $tenant->save();
                break;

            case 'complete':
                // Apply business preset with final selections
                $this->applyPreset($tenant, $tenant->business_type);
                return redirect('/');
        }

        return redirect()->route('onboarding.show', ['step' => $next]);
    }

    protected function getCurrentTenant()
    {
        $admin = Auth::guard('admin')->user();
        if (! $admin) {
            return null;
        }

        return $admin->tenants()->first();
    }

    protected function applyPreset($tenant, ?string $presetCode): void
    {
        if (! $presetCode) {
            return;
        }

        try {
            $preset = $this->presetRegistry->get($presetCode);
            if ($preset) {
                app(\Webkul\BusinessPreset\Helpers\PresetApplier::class)->apply($preset);

                if (! $tenant->theme || $tenant->theme === 'minimal-luxury') {
                    $tenant->theme = $preset->getRecommendedTheme();
                }
                if (! $tenant->template || $tenant->template === 'fashion') {
                    $tenant->template = $preset->getRecommendedTemplate();
                }
                $tenant->save();
            }
        } catch (\Exception $e) {
            report($e);
        }
    }
}
