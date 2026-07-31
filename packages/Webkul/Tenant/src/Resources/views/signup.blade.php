<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>شروع رایگان - ساتورا</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Vazirmatn',sans-serif;background:#f8fafc;color:#1e293b;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem}
        .card{background:white;border-radius:20px;padding:3rem 2rem;max-width:480px;width:100%;box-shadow:0 8px 40px rgba(0,0,0,.08)}
        .card h1{font-size:1.8rem;font-weight:800;text-align:center;margin-bottom:.5rem}
        .card p{text-align:center;color:#64748b;margin-bottom:2rem;font-size:.95rem}
        .form-group{margin-bottom:1.25rem}
        label{display:block;font-weight:600;margin-bottom:.4rem;font-size:.9rem}
        input,select{width:100%;padding:.75rem 1rem;border:1px solid #e2e8f0;border-radius:10px;font-family:'Vazirmatn',sans-serif;font-size:.95rem;transition:border .2s}
        input:focus,select:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
        button{width:100%;padding:.85rem;background:#6366f1;color:white;border:none;border-radius:12px;font-family:'Vazirmatn',sans-serif;font-size:1rem;font-weight:700;cursor:pointer;transition:all .3s}
        button:hover{background:#4f46e5;transform:translateY(-1px)}
        .error{color:#ef4444;font-size:.85rem;margin-bottom:1rem;text-align:center}
        .note{text-align:center;color:#94a3b8;font-size:.8rem;margin-top:1.5rem}
    </style>
</head>
<body>
<div class="card">
    <h1>ساخت فروشگاه جدید</h1>
    <p>اطلاعات فروشگاه خود را وارد کنید تا در چند دقیقه آماده شود.</p>

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('signup.store') }}">
        @csrf

        <div class="form-group">
            <label>نام فروشگاه</label>
            <input type="text" name="store_name" value="{{ old('store_name') }}" placeholder="مثلاً: فروشگاه مد مینا" required>
        </div>

        <div class="form-group">
            <label>آدرس فروشگاه (slug)</label>
            <input type="text" name="slug" value="{{ old('slug') }}" placeholder="mina-fashion" required pattern="[a-z0-9-]+">
        </div>

        <div class="form-group">
            <label>نوع کسب‌وکار</label>
            <select name="business_type">
                <option value="">انتخاب کنید...</option>
                <option value="fashion" {{ old('business_type')=='fashion'?'selected':'' }}>👗 مد و پوشاک</option>
                <option value="electronics" {{ old('business_type')=='electronics'?'selected':'' }}>📱 الکترونیک</option>
                <option value="grocery" {{ old('business_type')=='grocery'?'selected':'' }}>🛒 مواد غذایی</option>
                <option value="beauty" {{ old('business_type')=='beauty'?'selected':'' }}>💄 آرایشی</option>
                <option value="restaurant" {{ old('business_type')=='restaurant'?'selected':'' }}>🍽️ رستوران</option>
                <option value="digital" {{ old('business_type')=='digital'?'selected':'' }}>💻 دیجیتال</option>
                <option value="marketplace" {{ old('business_type')=='marketplace'?'selected':'' }}>🏪 مارکت‌پلیس</option>
                <option value="services" {{ old('business_type')=='services'?'selected':'' }}>🛠️ خدمات</option>
            </select>
        </div>

        <div class="form-group">
            <label>زبان</label>
            <select name="locale">
                <option value="fa" selected>فارسی</option>
                <option value="en">English</option>
                <option value="ar">العربية</option>
                <option value="tr">Türkçe</option>
            </select>
        </div>

        <div class="form-group">
            <label>ایمیل مدیر</label>
            <input type="email" name="admin_email" value="{{ old('admin_email') }}" placeholder="admin@example.com" required>
        </div>

        <div class="form-group">
            <label>رمز عبور</label>
            <input type="password" name="admin_password" placeholder="حداقل ۸ کاراکتر" required minlength="8">
        </div>

        <button type="submit">شروع رایگان — ساخت فروشگاه</button>
    </form>

    <p class="note">با ثبت‌نام، با شرایط استفاده و سیاست حریم خصوصی موافقت می‌کنید.</p>
</div>
</body>
</html>
