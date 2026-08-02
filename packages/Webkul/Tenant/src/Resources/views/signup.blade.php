<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('tenant::signup.title') }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Vazirmatn',sans-serif;background:linear-gradient(135deg,#f8fafc 0%,#e2e8f0 100%);color:#1e293b;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem}
        .card{background:white;border-radius:20px;padding:2.5rem 2rem;max-width:440px;width:100%;box-shadow:0 8px 40px rgba(0,0,0,.1)}
        .card h1{font-size:1.8rem;font-weight:800;text-align:center;margin-bottom:.25rem}
        .card .sub{text-align:center;color:#64748b;margin-bottom:2rem;font-size:.95rem}
        .form-group{margin-bottom:1.1rem}
        label{display:block;font-weight:600;margin-bottom:.35rem;font-size:.9rem}
        input{width:100%;padding:.75rem 1rem;border:2px solid #e2e8f0;border-radius:10px;font-family:'Vazirmatn',sans-serif;font-size:.95rem;transition:border .2s;background:#f8fafc}
        input:focus{outline:none;border-color:#6366f1;background:white;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
        .btn{width:100%;padding:.9rem;background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;border:none;border-radius:12px;font-family:'Vazirmatn',sans-serif;font-size:1.1rem;font-weight:700;cursor:pointer;transition:all .3s;margin-top:.5rem}
        .btn:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(99,102,241,.3)}
        .btn:disabled{opacity:.6;cursor:not-allowed;transform:none}
        .alert{padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.9rem;text-align:center}
        .alert-error{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}
        .alert-success{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0}
        .note{text-align:center;color:#94a3b8;font-size:.8rem;margin-top:1.25rem}
        .login-link{text-align:center;margin-top:1rem;font-size:.9rem}
        .login-link a{color:#6366f1;font-weight:600;text-decoration:none}
        .login-link a:hover{text-decoration:underline}
        .separator{display:flex;align-items:center;gap:1rem;margin:1.25rem 0;color:#94a3b8;font-size:.85rem}
        .separator::before,.separator::after{content:'';flex:1;height:1px;background:#e2e8f0}
        .alt-btn{width:100%;padding:.75rem;border:2px solid #e2e8f0;border-radius:10px;background:white;font-family:'Vazirmatn',sans-serif;font-size:.95rem;font-weight:500;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.5rem;transition:all .2s}
        .alt-btn:hover{border-color:#6366f1;background:#eef2ff}
        .spinner{display:inline-block;width:20px;height:20px;border:2px solid rgba(255,255,255,.3);border-top-color:white;border-radius:50%;animation:spin .6s linear infinite;margin-left:.5rem;vertical-align:middle}
        @keyframes spin{to{transform:rotate(360deg)}}
        @media(max-width:480px){
            .card{padding:1.5rem 1.25rem;border-radius:16px}
            .card h1{font-size:1.5rem}
            input{padding:.65rem .85rem;font-size:.9rem}
            .btn{font-size:1rem;padding:.8rem}
        }
    </style>
</head>
<body>
<div class="card">
    <h1>{{ __('tenant::signup.heading') }}</h1>
    <p class="sub">{{ __('tenant::signup.subtitle') }}</p>

    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Email + Password — primary method --}}
    <form method="POST" action="{{ route('signup.store') }}" id="signup-form">
        @csrf

        <div class="form-group">
            <label>{{ __('tenant::signup.name') }}</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('tenant::signup.name_placeholder') }}" required>
        </div>

        <div class="form-group">
            <label>{{ __('tenant::signup.email') }}</label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required dir="ltr" style="text-align:left">
        </div>

        <div class="form-group">
            <label>{{ __('tenant::signup.password') }}</label>
            <input type="password" name="password" placeholder="{{ __('tenant::signup.password_placeholder') }}" required minlength="8" dir="ltr" style="text-align:left">
        </div>

        <button type="submit" class="btn" id="submit-btn">
            <span id="btn-text">{{ __('tenant::signup.submit') }}</span>
        </button>
    </form>

    <div class="separator">{{ __('tenant::signup.or') }}</div>

    {{-- OTP via Phone --}}
    <form method="POST" action="{{ route('signup.otp.send') }}" id="otp-form">
        @csrf
        <div class="form-group">
            <label>{{ __('tenant::signup.phone') }}</label>
            <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="0912 345 6789" dir="ltr" style="text-align:left">
        </div>
        <button type="submit" class="alt-btn">
            📱 {{ __('tenant::signup.otp_button') }}
        </button>
    </form>

    <p class="note">{{ __('tenant::signup.terms') }}</p>

    <div class="login-link">
        {!! __('tenant::signup.has_account', ['url' => route('admin.session.create')]) !!}
    </div>
</div>

<script>
document.getElementById('signup-form').addEventListener('submit', function() {
    var btn = document.getElementById('submit-btn');
    var txt = document.getElementById('btn-text');
    btn.disabled = true;
    txt.innerHTML = '⏳ ' + '{{ __('tenant::signup.creating') }}' + ' <span class="spinner"></span>';
});
</script>
</body>
</html>
