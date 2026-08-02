<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>پنل سوپر ادمین — ساتورا</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Vazirmatn',sans-serif;background:#f1f5f9;color:#1e293b;min-height:100vh}
header{background:white;border-bottom:1px solid #e2e8f0;padding:1rem 2rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem}
header h1{font-size:1.3rem;font-weight:800;color:#6366f1}
.container{max-width:1200px;margin:0 auto;padding:2rem 1rem}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:2rem}
.stat-card{background:white;border-radius:14px;padding:1.5rem;text-align:center;border:1px solid #e2e8f0}
.stat-card .num{font-size:2rem;font-weight:800;color:#6366f1}
.stat-card .lbl{font-size:.85rem;color:#64748b;margin-top:.25rem}
table{width:100%;background:white;border-radius:14px;border-collapse:collapse;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.04)}
th,td{padding:.85rem 1rem;text-align:right;border-bottom:1px solid #e2e8f0}
th{background:#f8fafc;font-weight:700;font-size:.85rem;color:#64748b}
td{font-size:.9rem}
.badge{display:inline-block;padding:.25rem .75rem;border-radius:20px;font-size:.75rem;font-weight:600}
.badge-active{background:#dcfce7;color:#16a34a}
.badge-inactive{background:#fef2f2;color:#dc2626}
.btn-sm{padding:.4rem .85rem;border-radius:8px;font-size:.8rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.3rem}
.btn-impersonate{background:#6366f1;color:white}
.btn-impersonate:hover{background:#4f46e5}
.btn-create{background:#22c55e;color:white;padding:.6rem 1.25rem;font-size:.9rem}
.btn-back{color:#64748b;text-decoration:none;font-size:.9rem}
</style>
</head>
<body>

<header>
    <h1>⚡ پنل سوپر ادمین — ساتورا</h1>
    <div style="display:flex;gap:1rem;align-items:center">
        <a href="/admin" class="btn-back">← برگشت به ادمین</a>
        <a href="{{ route('super_admin.create') }}" class="btn-sm btn-create">+ تننت جدید</a>
    </div>
</header>

<div class="container">
    @if(session('success'))
        <div style="background:#dcfce7;color:#16a34a;padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem;text-align:center">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div style="background:#fef2f2;color:#dc2626;padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem;text-align:center">{{ $errors->first() }}</div>
    @endif

    <div class="stats">
        <div class="stat-card"><div class="num">{{ $tenants->count() }}</div><div class="lbl">کل تننت‌ها</div></div>
        <div class="stat-card"><div class="num">{{ $tenants->where('is_active', true)->count() }}</div><div class="lbl">فعال</div></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>نام فروشگاه</th>
                <th>نوع</th>
                <th>زبان</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tenants as $tenant)
            <tr>
                <td><strong>{{ $tenant->getName() }}</strong><br><small style="color:#94a3b8">{{ $tenant->getSlug() }}</small></td>
                <td>{{ $tenant->business_type ?? '—' }}</td>
                <td>{{ strtoupper($tenant->getLocale()) }}</td>
                <td><span class="badge {{ $tenant->isActive() ? 'badge-active' : 'badge-inactive' }}">{{ $tenant->isActive() ? 'فعال' : 'غیرفعال' }}</span></td>
                <td>
                    <a href="{{ route('super_admin.impersonate', $tenant->getId()) }}" class="btn-sm btn-impersonate" onclick="return confirm('وارد پنل این تننت می‌شوید. ادامه؟')">🔑 ورود</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

</body>
</html>
