<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>راه‌اندازی فروشگاه — ساتورا</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap">
<style>
*{margin:0;padding:0;box-sizing:border-box}
html{overflow-x:hidden}
body{font-family:'Vazirmatn',sans-serif;background:#f1f5f9;color:#1e293b;min-height:100vh}
.container{max-width:900px;margin:0 auto;padding:2rem 1rem}
.steps-bar{display:flex;justify-content:center;gap:.3rem;margin-bottom:2rem;flex-wrap:wrap}
.step-dot{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;border:2px solid #cbd5e1;background:white;color:#94a3b8;transition:all .3s}
.step-dot.active{background:#6366f1;border-color:#6366f1;color:white;transform:scale(1.1)}
.step-dot.done{background:#22c55e;border-color:#22c55e;color:white}
.step-line{width:24px;height:2px;background:#cbd5e1;align-self:center}
.step-line.done{background:#22c55e}
.card{background:white;border-radius:20px;padding:2.5rem 2rem;box-shadow:0 4px 24px rgba(0,0,0,.06);text-align:center}
.card h2{font-size:1.5rem;font-weight:800;margin-bottom:.5rem}
.card .sub{color:#64748b;margin-bottom:2rem;font-size:.95rem}
.form-row{display:flex;gap:1rem;margin-bottom:1rem;text-align:right}
.form-row input,.form-row textarea{flex:1;padding:.75rem 1rem;border:2px solid #e2e8f0;border-radius:10px;font-family:'Vazirmatn',sans-serif;font-size:.95rem;background:#f8fafc}
.form-row input:focus,.form-row textarea:focus{outline:none;border-color:#6366f1;background:white}
.grid-2{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem}
.grid-3{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem}
.grid-4{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.75rem}
.choice-card{padding:1.25rem;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;transition:all .2s;text-align:center}
.choice-card:hover{border-color:#6366f1;background:#eef2ff}
.choice-card.selected{border-color:#6366f1;background:#eef2ff;box-shadow:0 0 0 3px rgba(99,102,241,.15)}
.choice-card .icon{font-size:2rem;margin-bottom:.4rem}
.choice-card .name{font-weight:700;font-size:.95rem;margin-bottom:.2rem}
.choice-card .desc{font-size:.78rem;color:#64748b;line-height:1.4}
.choice-card .meta{font-size:.72rem;color:#94a3b8;margin-top:.3rem}
.hidden{display:none}
.btn{display:inline-flex;align-items:center;gap:.5rem;padding:.85rem 2.5rem;border-radius:12px;font-family:'Vazirmatn',sans-serif;font-size:1rem;font-weight:700;border:none;cursor:pointer;transition:all .3s;text-decoration:none}
.btn-primary{background:linear-gradient(135deg,#6366f1,#4f46e5);color:white}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(99,102,241,.3)}
.btn-success{background:linear-gradient(135deg,#22c55e,#16a34a);color:white;font-size:1.1rem;padding:1rem 3rem}
.btn-back{background:white;color:#64748b;border:2px solid #e2e8f0;margin-right:auto}
.type-card{padding:2rem;text-align:center;font-size:3rem}
.type-card .name{font-size:1.2rem;margin-top:.5rem}
.color-dots{display:flex;gap:4px;justify-content:center;margin-bottom:.4rem}
.color-dots span{width:16px;height:16px;border-radius:50%;border:1px solid #ddd}
@media(max-width:600px){
  .card{padding:1.5rem 1rem}
  .grid-2,.grid-3,.grid-4{grid-template-columns:repeat(2,1fr)}
  .step-dot{width:28px;height:28px;font-size:.65rem}
  .step-line{width:16px}
  .form-row{flex-direction:column}
  .type-card{font-size:2.5rem}
}
</style>
</head>
<body>
<div class="container">

<div class="steps-bar">
    @foreach($stepLabels as $i => $label)
        @php $stepCode = $steps[$i]; @endphp
        <div class="step-dot {{ $i < $stepIndex ? 'done' : '' }} {{ $currentStep === $stepCode ? 'active' : '' }}">{{ $i + 1 }}</div>
        @if($i < count($stepLabels) - 1) <div class="step-line {{ $i < $stepIndex ? 'done' : '' }}"></div> @endif
    @endforeach
</div>

{{-- STEP 1: Store or Marketplace --}}
<div class="card {{ $currentStep !== 'type' ? 'hidden' : '' }}">
    <h2>🏪 نوع پلتفرم خود را انتخاب کنید</h2>
    <p class="sub">فروشگاه تکی یا مارکت‌پلیس چند فروشنده؟</p>
    <form method="POST" action="{{ route('onboarding.store') }}">
        @csrf
        <input type="hidden" name="step" value="type">
        <input type="hidden" name="next" value="business-info">
        <div class="grid-2">
            <label class="choice-card type-card {{ ($tenant->modules['type'] ?? 'store') === 'store' ? 'selected' : '' }}">
                <input type="radio" name="store_type" value="store" {{ ($tenant->modules['type'] ?? 'store') === 'store' ? 'checked' : '' }} style="display:none">
                <div class="icon">🛍️</div>
                <div class="name">فروشگاه</div>
                <div class="desc">یک فروشگاه اختصاصی برای کسب‌وکار شما</div>
            </label>
            <label class="choice-card type-card {{ ($tenant->modules['type'] ?? '') === 'marketplace' ? 'selected' : '' }}">
                <input type="radio" name="store_type" value="marketplace" {{ ($tenant->modules['type'] ?? '') === 'marketplace' ? 'checked' : '' }} style="display:none">
                <div class="icon">🏪</div>
                <div class="name">مارکت‌پلیس</div>
                <div class="desc">پلتفرم چند فروشنده — فروشندگان فروشگاه خود را می‌سازند</div>
            </label>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:1.5rem">بعدی — اطلاعات کسب‌وکار ←</button>
    </form>
</div>

{{-- STEP 2: Business Info --}}
<div class="card {{ $currentStep !== 'business-info' ? 'hidden' : '' }}">
    <h2>📋 اطلاعات کسب‌وکار</h2>
    <p class="sub">چند جزئیات دیگر درباره فروشگاه خود وارد کنید</p>
    <form method="POST" action="{{ route('onboarding.store') }}">
        @csrf
        <input type="hidden" name="step" value="business-info">
        <input type="hidden" name="next" value="preset">
        <div class="form-row">
            <input type="text" name="mobile" value="{{ $tenant->mobile }}" placeholder="📱 شماره تماس" dir="ltr">
        </div>
        <div class="form-row">
            <textarea name="address" placeholder="📍 آدرس فروشگاه">{{ $tenant->address }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">بعدی — انتخاب نیش ←</button>
    </form>
</div>

{{-- STEP 3: Niche/Preset --}}
<div class="card {{ $currentStep !== 'preset' ? 'hidden' : '' }}">
    <h2>🎯 نیش (Niche) کسب‌وکار خود را انتخاب کنید</h2>
    <p class="sub">ما بر اساس نیش شما، قالب‌ها، دسته‌بندی‌ها و تنظیمات را آماده می‌کنیم</p>
    <form method="POST" action="{{ route('onboarding.store') }}">
        @csrf
        <input type="hidden" name="step" value="preset">
        <input type="hidden" name="next" value="template">
        <div class="grid-3">
            @foreach($presets as $preset)
            @php $icons = ['fashion'=>'👗','electronics'=>'📱','grocery'=>'🛒','beauty'=>'💄','digital'=>'💻','furniture'=>'🪑','marketplace'=>'🏪','custom'=>'✨']; @endphp
            <label class="choice-card {{ $tenant->business_type === $preset->getCode() ? 'selected' : '' }}">
                <input type="radio" name="preset_code" value="{{ $preset->getCode() }}" {{ $tenant->business_type === $preset->getCode() ? 'checked' : '' }} style="display:none">
                <div class="icon">{{ $icons[$preset->getCode()] ?? '📦' }}</div>
                <div class="name">{{ $preset->getName() }}</div>
                <div class="desc">{{ Str::limit($preset->getDescription(), 50) }}</div>
            </label>
            @endforeach
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:1.5rem">بعدی — انتخاب قالب ←</button>
    </form>
</div>

{{-- STEP 4: Templates --}}
<div class="card {{ $currentStep !== 'template' ? 'hidden' : '' }}">
    <h2>📐 قالب فروشگاه</h2>
    <p class="sub">ساختار و چیدمان صفحات — {{ count($compatibleTemplates ?? []) }} قالب سازگار با نیش شما</p>
    <form method="POST" action="{{ route('onboarding.store') }}">
        @csrf
        <input type="hidden" name="step" value="template">
        <input type="hidden" name="next" value="theme">
        <div class="grid-2">
            @forelse($compatibleTemplates ?? [] as $tpl)
            @php $meta = $templateMeta[$tpl] ?? ['name'=>$tpl, 'desc'=>'', 'sections'=>4]; @endphp
            <label class="choice-card {{ $tenant->template === $tpl ? 'selected' : '' }}">
                <input type="radio" name="template" value="{{ $tpl }}" {{ $tenant->template === $tpl ? 'checked' : '' }} style="display:none">
                <div class="icon">📐</div>
                <div class="name">{{ $meta['name'] }}</div>
                <div class="desc">{{ $meta['desc'] }}</div>
                <div class="meta">{{ $meta['sections'] }} بخش — پیش‌نمایش ساختار صفحه</div>
            </label>
            @empty
            <p>قالبی برای این نیش یافت نشد.</p>
            @endforelse
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:1.5rem">بعدی — انتخاب تم ←</button>
    </form>
</div>

{{-- STEP 5: Theme --}}
<div class="card {{ $currentStep !== 'theme' ? 'hidden' : '' }}">
    <h2>🎨 تم فروشگاه</h2>
    <p class="sub">رنگ‌ها، فونت و هویت بصری</p>
    <form method="POST" action="{{ route('onboarding.store') }}">
        @csrf
        <input type="hidden" name="step" value="theme">
        <input type="hidden" name="next" value="complete">
        <div class="grid-3">
            @foreach([
                'minimal-luxury' => ['name'=>'مینیمال لوکس', 'colors'=>'#6366f1,#06b6d4,#f8fafc', 'desc'=>'ساده، شیک، حرفه‌ای'],
                'modern-dark' => ['name'=>'مدرن دارک', 'colors'=>'#0f172a,#6366f1,#1e293b', 'desc'=>'تیره، مدرن، تکنولوژی'],
                'colorful' => ['name'=>'رنگارنگ', 'colors'=>'#f59e0b,#ef4444,#fef3c7', 'desc'=>'شاد، پرانرژی، دوستانه'],
            ] as $code => $t)
            <label class="choice-card {{ $tenant->theme === $code ? 'selected' : '' }}">
                <input type="radio" name="theme" value="{{ $code }}" {{ $tenant->theme === $code ? 'checked' : '' }} style="display:none">
                <div class="color-dots">
                    @foreach(explode(',', $t['colors']) as $c)
                    <span style="background:{{ $c }}"></span>
                    @endforeach
                </div>
                <div class="name">{{ $t['name'] }}</div>
                <div class="desc">{{ $t['desc'] }}</div>
            </label>
            @endforeach
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:1.5rem">🎉 تکمیل راه‌اندازی ←</button>
    </form>
</div>

{{-- STEP 6: Done --}}
<div class="card {{ $currentStep !== 'complete' ? 'hidden' : '' }}">
    <div style="font-size:5rem;margin-bottom:1rem">🎉</div>
    <h2>فروشگاه شما آماده است!</h2>
    <p class="sub">همه چیز تنظیم شد — حالا می‌توانید فروشگاه خود را ببینید و مدیریت کنید</p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;margin-top:1.5rem">
        <a href="/" class="btn btn-success">🛍️ مشاهده فروشگاه</a>
        <a href="/admin" class="btn btn-primary">⚙️ پنل مدیریت</a>
    </div>
</div>

</div>

<script>
document.querySelectorAll('.choice-card').forEach(card => {
    card.addEventListener('click', function() {
        this.querySelector('input[type=radio]').checked = true;
        this.closest('.card').querySelectorAll('.choice-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
    });
});
</script>
</body>
</html>
