<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ساتورا — پلتفرم فروشگاهی هوشمند</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&amp;display=swap">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Vazirmatn',sans-serif;background:#f8fafc;color:#1e293b;line-height:1.7}
html{scroll-behavior:smooth}
.nav{position:fixed;top:0;left:0;right:0;z-index:100;background:rgba(255,255,255,.94);backdrop-filter:blur(12px);border-bottom:1px solid #e2e8f0}
.nav-inner{max-width:1200px;margin:0 auto;padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;height:64px}
.nav-logo{font-size:1.5rem;font-weight:800;color:#6366f1;text-decoration:none}
.nav-links{display:flex;gap:2rem;align-items:center}
.nav-links a{text-decoration:none;color:#64748b;font-weight:500;font-size:.95rem}
.hero{padding:160px 1.5rem 100px;text-align:center;max-width:950px;margin:0 auto}
.hero h1{font-size:clamp(2rem,5vw,3.5rem);font-weight:800;line-height:1.5;margin-bottom:1.5rem}
.hero h1 span{color:#6366f1}
.hero p{font-size:1.15rem;color:#64748b;max-width:650px;margin:0 auto 2.5rem}
.hero-btns{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap}
.btn-primary{display:inline-flex;align-items:center;gap:.5rem;padding:.85rem 2rem;border-radius:12px;background:#6366f1;color:white;font-weight:700;font-size:1rem;text-decoration:none;transition:all .3s}
.btn-primary:hover{background:#4f46e5;transform:translateY(-2px)}
.btn-outline{display:inline-flex;align-items:center;gap:.5rem;padding:.85rem 2rem;border-radius:12px;border:2px solid #6366f1;color:#6366f1;font-weight:700;font-size:1rem;text-decoration:none;transition:all .3s}
.btn-outline:hover{background:#6366f1;color:white}
.section{padding:100px 1.5rem;max-width:1200px;margin:0 auto}
.section-header{text-align:center;margin-bottom:4rem}
.section-header h2{font-size:2.2rem;font-weight:800;margin-bottom:.75rem}
.section-header p{color:#64748b;font-size:1.05rem;max-width:550px;margin:0 auto}
.features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:2rem}
.feature-card{background:white;border:1px solid #e2e8f0;border-radius:16px;padding:2rem;transition:all .3s}
.feature-card:hover{transform:translateY(-4px);box-shadow:0 12px 40px rgba(0,0,0,.08)}
.feature-icon{font-size:2.5rem;margin-bottom:1rem}
.feature-card h3{font-size:1.2rem;font-weight:700;margin-bottom:.5rem}
.feature-card p{color:#64748b;font-size:.95rem}
.bg-white{background:white}
.presets-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1.25rem}
.preset-card{text-align:center;padding:1.5rem;border:1px solid #e2e8f0;border-radius:14px;transition:all .3s;cursor:pointer}
.preset-card:hover{border-color:#6366f1;transform:translateY(-3px)}
.preset-icon{font-size:2.5rem;margin-bottom:.5rem}
.preset-card h4{font-weight:700;font-size:1rem}
.pricing-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:2rem}
.pricing-card{background:white;border:1px solid #e2e8f0;border-radius:20px;padding:2.5rem 2rem;text-align:center;position:relative}
.pricing-card.featured{border-color:#6366f1;box-shadow:0 8px 40px rgba(99,102,241,.15)}
.pricing-badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:#6366f1;color:white;padding:.3rem 1.25rem;border-radius:20px;font-size:.8rem;font-weight:700}
.pricing-card h3{font-size:1.3rem;font-weight:700;margin-bottom:.5rem}
.pricing-price{font-size:2.8rem;font-weight:800;color:#6366f1;margin:1rem 0}
.pricing-price small{font-size:1rem;font-weight:400;color:#64748b}
.pricing-features{list-style:none;text-align:right;margin:1.5rem 0}
.pricing-features li{padding:.5rem 0;font-size:.95rem;color:#64748b}
.cta-wrap{max-width:1200px;margin:0 auto 60px;padding:0 1.5rem}
.cta-box{background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;text-align:center;padding:80px 2rem;border-radius:24px}
.cta-box h2{font-size:2.2rem;font-weight:800;margin-bottom:1rem}
.cta-box p{font-size:1.05rem;opacity:.9;margin-bottom:2rem}
.cta-box .btn-primary{background:white;color:#6366f1}
.cta-box .btn-primary:hover{background:#f1f5f9}
.footer{padding:60px 1.5rem 40px;border-top:1px solid #e2e8f0;text-align:center;color:#64748b;font-size:.9rem}

/* Responsive */
@media(max-width:768px){
  .nav-links{display:none}
  .hero{padding:120px 1.5rem 60px}
  .hero h1{font-size:1.6rem}
  .section{padding:60px 1.5rem}
  .section-header h2{font-size:1.6rem}
  .features-grid{grid-template-columns:1fr}
  .pricing-grid{grid-template-columns:1fr}
  .presets-grid{grid-template-columns:repeat(2,1fr)}
  .pricing-price{font-size:2rem}
  .cta-box{padding:50px 1.5rem}
}
@media(max-width:480px){
  .presets-grid{grid-template-columns:repeat(2,1fr)}
  .hero-btns{flex-direction:column}
}
</style>
</head>
<body>

<nav class="nav">
  <div class="nav-inner">
    <a href="/" class="nav-logo">ساتورا</a>
    <div class="nav-links">
      <a href="#features">ویژگی‌ها</a>
      <a href="#presets">کسب‌وکارها</a>
      <a href="#pricing">قیمت‌ها</a>
    </div>
  </div>
</nav>

<section class="hero">
  <h1>فروشگاه آنلاین خود را در <span>چند دقیقه</span> بسازید</h1>
  <p>ساتورا یک پلتفرم فروشگاهی کامل است. نوع کسب‌وکار خود را انتخاب کنید و فروشگاه حرفه‌ای خود را تحویل بگیرید. بدون کدنویسی، بدون دردسر.</p>
  <div class="hero-btns">
    <a href="{{ url('/signup') }}" class="btn-primary">🚀 شروع رایگان</a>
    <a href="{{ url('/install') }}" class="btn-outline">🎮 مشاهده نسخه نمایشی</a>
  </div>
</section>

<section id="features" class="section">
  <div class="section-header">
    <h2>همه چیز برای فروش آنلاین</h2>
    <p>از مدیریت محصولات تا پرداخت و ارسال، هر آنچه نیاز دارید در ساتورا آماده است.</p>
  </div>
  <div class="features-grid">
    <div class="feature-card"><div class="feature-icon">🎨</div><h3>قالب‌های حرفه‌ای</h3><p>۳ قالب بصری و ۴ طرح صفحه برای هر نوع کسب‌وکار. ترکیب آزاد قالب و طرح.</p></div>
    <div class="feature-card"><div class="feature-icon">🌍</div><h3>چندزبانه کامل</h3><p>پشتیبانی از فارسی، عربی، ترکی و انگلیسی با چیدمان راست‌به‌چپ و چپ‌به‌راست خودکار.</p></div>
    <div class="feature-card"><div class="feature-icon">🏪</div><h3>پیش‌فرض‌های کسب‌وکار</h3><p>۹ نوع کسب‌وکار آماده: مد، الکترونیک، مواد غذایی، آرایشی، رستوران و بیشتر.</p></div>
    <div class="feature-card"><div class="feature-icon">🤖</div><h3>آماده هوش مصنوعی</h3><p>معماری آماده برای تولید خودکار وب‌سایت با هوش مصنوعی بر اساس نوع کسب‌وکار شما.</p></div>
    <div class="feature-card"><div class="feature-icon">💳</div><h3>پرداخت امن</h3><p>درگاه‌های پرداخت متنوع شامل پی‌پال، استرایپ و درگاه‌های داخلی با امنیت بالا.</p></div>
    <div class="feature-card"><div class="feature-icon">📱</div><h3>واکنش‌گرا و مدرن</h3><p>طراحی کاملاً واکنش‌گرا برای موبایل، تبلت و دسکتاپ با تجربه کاربری عالی.</p></div>
  </div>
</section>

<section id="presets" class="section bg-white">
  <div class="section-header">
    <h2>برای هر کسب‌وکاری آماده است</h2>
    <p>نوع کسب‌وکار خود را انتخاب کنید. ما بقیه کارها را انجام می‌دهیم.</p>
  </div>
  <div class="presets-grid">
    <div class="preset-card"><div class="preset-icon">👗</div><h4>مد و پوشاک</h4></div>
    <div class="preset-card"><div class="preset-icon">📱</div><h4>الکترونیک</h4></div>
    <div class="preset-card"><div class="preset-icon">🛒</div><h4>مواد غذایی</h4></div>
    <div class="preset-card"><div class="preset-icon">💄</div><h4>آرایشی و بهداشتی</h4></div>
    <div class="preset-card"><div class="preset-icon">🍽️</div><h4>رستوران و غذا</h4></div>
    <div class="preset-card"><div class="preset-icon">💻</div><h4>محصولات دیجیتال</h4></div>
    <div class="preset-card"><div class="preset-icon">🏪</div><h4>مارکت‌پلیس</h4></div>
    <div class="preset-card"><div class="preset-icon">🛠️</div><h4>خدمات</h4></div>
  </div>
</section>

<section id="pricing" class="section">
  <div class="section-header">
    <h2>قیمت‌گذاری ساده و شفاف</h2>
    <p>یک قیمت. تمام امکانات. بدون هزینه پنهان.</p>
  </div>
  <div class="pricing-grid">
    <div class="pricing-card">
      <h3>پایه</h3>
      <div class="pricing-price">رایگان</div>
      <ul class="pricing-features">
        <li>✓ ۵۰ محصول</li><li>✓ ۱ زبان</li><li>✓ قالب پیش‌فرض</li><li>✓ پشتیبانی ایمیل</li>
      </ul>
      <a href="{{ url('/signup') }}" class="btn-primary" style="width:100%;justify-content:center">شروع رایگان</a>
    </div>
    <div class="pricing-card featured">
      <div class="pricing-badge">محبوب</div>
      <h3>حرفه‌ای</h3>
      <div class="pricing-price">۲۹ دلار <small>/ ماه</small></div>
      <ul class="pricing-features">
        <li>✓ محصولات نامحدود</li><li>✓ ۴ زبان</li><li>✓ تمام قالب‌ها</li><li>✓ پیش‌فرض‌های کسب‌وکار</li><li>✓ پشتیبانی اولویت‌دار</li><li>✓ دامنه اختصاصی</li>
      </ul>
      <a href="{{ url('/signup') }}" class="btn-primary" style="width:100%;justify-content:center">شروع رایگان</a>
    </div>
    <div class="pricing-card">
      <h3>سازمانی</h3>
      <div class="pricing-price">۹۹ دلار <small>/ ماه</small></div>
      <ul class="pricing-features">
        <li>✓ همه امکانات حرفه‌ای</li><li>✓ چند فروشگاهی</li><li>✓ API اختصاصی</li><li>✓ گزارش‌های پیشرفته</li><li>✓ پشتیبانی ۲۴/۷</li>
      </ul>
      <a href="{{ url('/signup') }}" class="btn-primary" style="width:100%;justify-content:center">شروع رایگان</a>
    </div>
  </div>
</section>

<div class="cta-wrap">
  <div class="cta-box">
    <h2>آماده شروع هستید؟</h2>
    <p>همین امروز فروشگاه خود را بسازید. ۱۴ روز ضمانت بازگشت وجه.</p>
    <a href="{{ url('/signup') }}" class="btn-primary">🚀 شروع رایگان</a>
  </div>
</div>

<footer class="footer">
  <p>© ۲۰۲۶ ساتورا. تمام حقوق محفوظ است.</p>
  <p style="margin-top:.5rem">ساخته شده با لاراول و Nuxt</p>
</footer>

</body>
</html>
