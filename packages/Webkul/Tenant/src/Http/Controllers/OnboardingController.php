<?php

namespace Webkul\Tenant\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Webkul\BusinessPreset\Helpers\PresetApplier;
use Webkul\BusinessPreset\Helpers\PresetRegistry;
use Webkul\Tenant\Repositories\TenantRepository;

class OnboardingController extends Controller
{
    protected array $steps = ['type', 'business-info', 'preset', 'template', 'theme', 'complete'];

    /**
     * Templates per niche — each niche gets 3+ compatible templates.
     */
    protected array $nicheTemplates = [
        'fashion' => ['fashion', 'general', 'minimal'],
        'electronics' => ['electronics', 'general', 'tech'],
        'grocery' => ['grocery', 'general', 'fresh'],
        'beauty' => ['beauty', 'fashion', 'minimal'],
        'digital' => ['electronics', 'tech', 'general'],
        'furniture' => ['furniture', 'general', 'minimal'],
        'diverse' => ['general', 'minimal', 'fashion'],
        'custom' => ['general', 'minimal', 'fashion'],
    ];

    protected array $templateMeta = [
        'fashion' => ['name' => 'مد و پوشاک', 'desc' => 'اسلایدر تصاویر، مجموعه‌های فصلی، لوک‌بوک، گالری مدلها', 'sections' => 8],
        'electronics' => ['name' => 'الکترونیک', 'desc' => 'مقایسه محصولات، صفحه برندها، مشخصات فنی، بخش تخفیف‌ها', 'sections' => 7],
        'grocery' => ['name' => 'سوپرمارکت', 'desc' => 'دسته‌بندی محصولات، پیشنهاد روز، سبد خرید سریع، تخفیف‌های هفتگی', 'sections' => 6],
        'general' => ['name' => 'عمومی', 'desc' => 'صفحه اصلی چندمنظوره، بخش محصولات ویژه، دسته‌بندی‌ها', 'sections' => 5],
        'minimal' => ['name' => 'مینیمال', 'desc' => 'طراحی ساده و تمیز، فوکوس روی محصول، ناوبری سبک', 'sections' => 4],
        'tech' => ['name' => 'تکنولوژی', 'desc' => 'صفحه مقایسه، جدول مشخصات، بخش گیمینگ، مقالات تخصصی', 'sections' => 7],
        'fresh' => ['name' => 'تازه و طبیعی', 'desc' => 'طرح ارگانیک، محصولات تازه، دسته‌بندی رنگی، پیشنهاد سرآشپز', 'sections' => 6],
        'beauty' => ['name' => 'زیبایی و لوکس', 'desc' => 'گالری محصولات، راهنمای خرید، بخش برندها، اینفلوئنسرها', 'sections' => 7],
        'furniture' => ['name' => 'مبلمان و دکور', 'desc' => 'گالری طرح‌ها، اتاق‌های نمایشی، مشاوره رایگان، کاتالوگ', 'sections' => 6],
        'marketplace' => ['name' => 'مارکت‌پلیس', 'desc' => 'صفحه فروشندگان، محصولات پرطرفدار، پیشنهادات ویژه', 'sections' => 8],
    ];

    public function __construct(
        protected TenantRepository $tenantRepository,
        protected PresetRegistry $presetRegistry
    ) {}

    public function show(Request $request, ?string $step = null)
    {
        $tenant = $this->getCurrentTenant();
        if (! $tenant) {
            return redirect()->route('signup.show');
        }

        $currentStep = $step ?: 'type';
        $stepIndex = array_search($currentStep, $this->steps);

        $data = [
            'tenant' => $tenant,
            'tenantUrl' => $this->buildTenantUrl($tenant),
            'currentStep' => $currentStep,
            'stepIndex' => $stepIndex,
            'steps' => $this->steps,
            'presets' => $this->presetRegistry->all(),
            'icons' => ['fashion' => '👗', 'electronics' => '📱', 'grocery' => '🛒', 'beauty' => '💄', 'digital' => '💻', 'furniture' => '🪑', 'diverse' => '🌟', 'custom' => '✨'],
            'stepLabels' => ['نوع', 'اطلاعات', 'نیش', 'قالب', 'تم', 'پایان'],
        ];

        // Get compatible templates for the selected preset
        if ($tenant->business_type && isset($this->nicheTemplates[$tenant->business_type])) {
            $data['compatibleTemplates'] = $this->nicheTemplates[$tenant->business_type];
            $data['templateMeta'] = $this->templateMeta;
        }

        return view('tenant::onboarding.wizard', $data);
    }

    public function store(Request $request)
    {
        $tenant = $this->getCurrentTenant();
        if (! $tenant) {
            return redirect()->route('signup.show');
        }

        $step = $request->input('step');
        $next = $request->input('next');

        switch ($step) {
            case 'type':
                $tenant->modules = ['type' => $request->input('store_type', 'store')];
                $tenant->save();
                break;

            case 'business-info':
                // Map store_name to tenant name
                $updateData = $request->only(['slug', 'mobile', 'address']);
                if ($request->filled('store_name')) {
                    $updateData['name'] = $request->input('store_name');
                }
                $tenant->update($updateData);

                // Update admin user name to match store name if admin was auto-named
                if ($request->filled('store_name')) {
                    $admin = Auth::guard('admin')->user();
                    if ($admin) {
                        $admin->update(['name' => $request->input('store_name')]);
                    }
                }
                break;

            case 'preset':
                $tenant->business_type = $request->input('preset_code');
                $tenant->save();
                $this->applyPreset($tenant, $request->input('preset_code'));
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
                $this->applyPreset($tenant, $tenant->business_type);

                return redirect()->away($this->buildTenantUrl($tenant));
        }

        return redirect()->route('onboarding.show', ['step' => $next]);
    }

    protected function getCurrentTenant()
    {
        $admin = Auth::guard('admin')->user();

        return $admin ? $admin->tenants()->first() : null;
    }

    protected function applyPreset($tenant, ?string $presetCode): void
    {
        if (! $presetCode) {
            return;
        }

        try {
            $preset = $this->presetRegistry->get($presetCode);
            if ($preset) {
                app(PresetApplier::class)->apply($preset);
                $tenant->theme = $tenant->theme ?: $preset->getRecommendedTheme();
                $tenant->template = $tenant->template ?: $preset->getRecommendedTemplate();
                $tenant->save();
            }
        } catch (\Exception $e) {
            report($e);
        }
    }

    /**
     * Build the tenant's storefront URL.
     * Local:   http://satora.test/shop/{slug}
     * Prod:    http://{slug}.satora.test
     */
    protected function buildTenantUrl($tenant): string
    {
        $appUrl = config('app.url', 'http://satora.test');
        $parsed = parse_url($appUrl);
        $host = $parsed['host'] ?? 'satora.test';
        $scheme = $parsed['scheme'] ?? 'http';
        $port = isset($parsed['port']) ? ':'.$parsed['port'] : '';

        if (app()->environment('local')) {
            return sprintf('%s://%s%s/shop/%s', $scheme, $host, $port, $tenant->slug);
        }

        return sprintf('%s://%s.%s%s', $scheme, $tenant->slug, $host, $port);
    }
}
