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
.steps-bar{display:flex;justify-content:center;gap:.5rem;margin-bottom:2rem;flex-wrap:wrap}
.step-dot{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;transition:all .3s;border:2px solid #cbd5e1;background:white;color:#94a3b8}
.step-dot.active{background:#6366f1;border-color:#6366f1;color:white}
.step-dot.done{background:#22c55e;border-color:#22c55e;color:white}
.step-line{width:40px;height:2px;background:#cbd5e1;align-self:center}
.step-line.done{background:#22c55e}
.card{background:white;border-radius:20px;padding:2.5rem 2rem;box-shadow:0 4px 24px rgba(0,0,0,.06);text-align:center}
.card h2{font-size:1.6rem;font-weight:800;margin-bottom:.5rem}
.card p{color:#64748b;margin-bottom:2rem;font-size:1rem}
.form-row{display:flex;gap:1rem;margin-bottom:1rem;text-align:right}
.form-row input,.form-row textarea{flex:1;padding:.75rem 1rem;border:2px solid #e2e8f0;border-radius:10px;font-family:'Vazirmatn',sans-serif;font-size:.95rem;transition:border .2s;background:#f8fafc}
.form-row input:focus,.form-row textarea:focus{outline:none;border-color:#6366f1;background:white}
textarea{resize:vertical;min-height:80px}
.grid-2{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;text-align:center}
.grid-3{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem}
.choice-card{padding:1.5rem;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;transition:all .2s}
.choice-card:hover{border-color:#6366f1;background:#eef2ff}
.choice-card.selected{border-color:#6366f1;background:#eef2ff;box-shadow:0 0 0 3px rgba(99,102,241,.15)}
.choice-card .icon{font-size:2.5rem;margin-bottom:.5rem}
.choice-card .name{font-weight:700;font-size:1rem;margin-bottom:.25rem}
.choice-card .desc{font-size:.8rem;color:#64748b;line-height:1.4}
.btn{display:inline-flex;align-items:center;gap:.5rem;padding:.85rem 2.5rem;border-radius:12px;font-family:'Vazirmatn',sans-serif;font-size:1rem;font-weight:700;border:none;cursor:pointer;transition:all .3s;text-decoration:none}
.btn-primary{background:linear-gradient(135deg,#6366f1,#4f46e5);color:white}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(99,102,241,.3)}
.btn-secondary{background:white;color:#6366f1;border:2px solid #6366f1}
.btn-success{background:linear-gradient(135deg,#22c55e,#16a34a);color:white;font-size:1.2rem;padding:1rem 3rem}
.hidden{display:none}
.complete-icon{font-size:5rem;margin-bottom:1rem}
@media(max-width:600px){
  .form-row{flex-direction:column}
  .card{padding:1.5rem 1rem}
  .grid-2,.grid-3{grid-template-columns:repeat(2,1fr)}
  .step-dot{width:32px;height:32px;font-size:.7rem}
  .step-line{width:20px}
}
</style>
</head>
<body>
<div class="container">

<div class="steps-bar">
    @foreach(['اطلاعات', 'نوع کسب‌وکار', 'قالب', 'تم', 'پایان'] as $i => $label)
        @php $stepCode = ['business-info','preset','template','theme','complete'][$i]; @endphp
        <div class="step-dot {{ $i < $stepIndex ? 'done' : '' }} {{ $currentStep === $stepCode ? 'active' : '' }}">{{ $i + 1 }}</div>
        @if($i < 4) <div class="step-line {{ $i < $stepIndex ? 'done' : '' }}"></div> @endif
    @endforeach
</div>

{{-- STEP 1: Business Info --}}
<div class="card {{ $currentStep !== 'business-info' ? 'hidden' : '' }}" id="step-business-info">
    <h2>📋 اطلاعات کسب‌وکار</h2>
    <p>چند جزئیات دیگر درباره فروشگاه خود وارد کنید</p>
    <form method="POST" action="{{ route('onboarding.store') }}">
        @csrf
        <input type="hidden" name="step" value="business-info">
        <input type="hidden" name="next" value="preset">
        <div class="form-row">
            <input type="text" name="mobile" value="{{ $tenant->mobile }}" placeholder="📱 شماره تماس فروشگاه" dir="ltr">
        </div>
        <div class="form-row">
            <textarea name="address" placeholder="📍 آدرس فروشگاه">{{ $tenant->address }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">بعدی — انتخاب نوع کسب‌وکار ←</button>
    </form>
</div>

{{-- STEP 2: Business Preset --}}
<div class="card {{ $currentStep !== 'preset' ? 'hidden' : '' }}" id="step-preset">
    <h2>🏪 نوع کسب‌وکار خود را انتخاب کنید</h2>
    <p>ما بقیه کارها را بر اساس انتخاب شما انجام می‌دهیم</p>
    <form method="POST" action="{{ route('onboarding.store') }}">
        @csrf
        <input type="hidden" name="step" value="preset">
        <input type="hidden" name="next" value="template">
        <div class="grid-3">
            @foreach($presets as $preset)
            <label class="choice-card {{ $tenant->business_type === $preset->getCode() ? 'selected' : '' }}">
                <input type="radio" name="preset_code" value="{{ $preset->getCode() }}" {{ $tenant->business_type === $preset->getCode() ? 'checked' : '' }} style="display:none">
                <div class="icon">{{ ['fashion'=>'👗','electronics'=>'📱','grocery'=>'🛒','beauty'=>'💄','restaurant'=>'🍽️','digital'=>'💻','marketplace'=>'🏪','services'=>'🛠️','custom'=>'✨'][$preset->getCode()] ?? '📦' }}</div>
                <div class="name">{{ $preset->getName() }}</div>
                <div class="desc">{{ Str::limit($preset->getDescription(), 60) }}</div>
            </label>
            @endforeach
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:1.5rem">بعدی — انتخاب قالب ←</button>
    </form>
</div>

{{-- STEP 3: Template --}}
<div class="card {{ $currentStep !== 'template' ? 'hidden' : '' }}" id="step-template">
    <h2>📐 قالب فروشگاه</h2>
    <p>ساختار و چیدمان صفحات فروشگاه خود را انتخاب کنید</p>
    <form method="POST" action="{{ route('onboarding.store') }}">
        @csrf
        <input type="hidden" name="step" value="template">
        <input type="hidden" name="next" value="theme">
        <div class="grid-2">
            @php
            $templates = [
                'fashion' => ['name'=>'مد و پوشاک', 'desc'=>'اسلایدر، مجموعه‌ها، لوک‌بوک'],
                'electronics' => ['name'=>'الکترونیک', 'desc'=>'مقایسه محصولات، برندها، تخفیف‌ها'],
                'grocery' => ['name'=>'سوپرمارکت', 'desc'=>'دسته‌بندی محصولات، پیشنهاد روز'],
                'general' => ['name'=>'عمومی', 'desc'=>'صفحه اصلی ساده و همه‌کاره'],
            ];
            @endphp
            @foreach($templates as $code => $t)
            <label class="choice-card {{ $tenant->template === $code ? 'selected' : '' }}">
                <input type="radio" name="template" value="{{ $code }}" {{ $tenant->template === $code ? 'checked' : '' }} style="display:none">
                <div class="icon">📐</div>
                <div class="name">{{ $t['name'] }}</div>
                <div class="desc">{{ $t['desc'] }}</div>
            </label>
            @endforeach
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:1.5rem">بعدی — انتخاب تم ←</button>
    </form>
</div>

{{-- STEP 4: Theme --}}
<div class="card {{ $currentStep !== 'theme' ? 'hidden' : '' }}" id="step-theme">
    <h2>🎨 تم فروشگاه</h2>
    <p>رنگ‌ها، فونت و هویت بصری فروشگاه خود را انتخاب کنید</p>
    <form method="POST" action="{{ route('onboarding.store') }}">
        @csrf
        <input type="hidden" name="step" value="theme">
        <input type="hidden" name="next" value="complete">
        <div class="grid-3">
            @php
            $themes = [
                'minimal-luxury' => ['name'=>'مینیمال لوکس', 'colors'=>'#6366f1,#06b6d4,#f8fafc', 'desc'=>'ساده، شیک، حرفه‌ای'],
                'modern-dark' => ['name'=>'مدرن دارک', 'colors'=>'#0f172a,#6366f1,#1e293b', 'desc'=>'تیره، مدرن، تکنولوژی'],
                'colorful' => ['name'=>'رنگارنگ', 'colors'=>'#f59e0b,#ef4444,#fef3c7', 'desc'=>'شاد، پرانرژی، دوستانه'],
            ];
            @endphp
            @foreach($themes as $code => $t)
            <label class="choice-card {{ $tenant->theme === $code ? 'selected' : '' }}">
                <input type="radio" name="theme" value="{{ $code }}" {{ $tenant->theme === $code ? 'checked' : '' }} style="display:none">
                <div style="display:flex;gap:4px;justify-content:center;margin-bottom:.5rem">
                    @foreach(explode(',', $t['colors']) as $color)
                    <div style="width:20px;height:20px;border-radius:50%;background:{{ $color }};border:1px solid #ddd"></div>
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

{{-- STEP 5: Done --}}
<div class="card {{ $currentStep !== 'complete' ? 'hidden' : '' }}" id="step-complete">
    <div class="complete-icon">🎉</div>
    <h2>فروشگاه شما آماده است!</h2>
    <p>همه چیز تنظیم شد. حالا می‌توانید فروشگاه خود را ببینید و مدیریت کنید.</p>
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
        document.querySelectorAll('.choice-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
    });
});
</script>
</body>
</html>
