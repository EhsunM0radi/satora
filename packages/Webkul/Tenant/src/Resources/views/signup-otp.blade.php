<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('tenant::signup.otp_title') }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Vazirmatn',sans-serif;background:linear-gradient(135deg,#f8fafc 0%,#e2e8f0 100%);color:#1e293b;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem}
        .card{background:white;border-radius:20px;padding:2.5rem 2rem;max-width:400px;width:100%;box-shadow:0 8px 40px rgba(0,0,0,.1);text-align:center}
        .card h1{font-size:1.6rem;font-weight:800;margin-bottom:.5rem}
        .card .sub{color:#64748b;margin-bottom:1.5rem;font-size:.95rem}
        .card .phone{background:#f1f5f9;padding:.5rem 1rem;border-radius:8px;display:inline-block;font-weight:600;margin-bottom:1.5rem;direction:ltr}
        .form-group{margin-bottom:1.1rem;text-align:right}
        label{display:block;font-weight:600;margin-bottom:.35rem;font-size:.9rem}
        input{width:100%;padding:.75rem 1rem;border:2px solid #e2e8f0;border-radius:10px;font-family:'Vazirmatn',sans-serif;font-size:.95rem;transition:border .2s;background:#f8fafc;text-align:center;letter-spacing:.5rem;font-size:1.4rem;direction:ltr}
        input:focus{outline:none;border-color:#6366f1;background:white;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
        .btn{width:100%;padding:.9rem;background:linear-gradient(135deg,#6366f1,#4f46e5);color:white;border:none;border-radius:12px;font-family:'Vazirmatn',sans-serif;font-size:1.1rem;font-weight:700;cursor:pointer;transition:all .3s}
        .btn:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(99,102,241,.3)}
        .btn:disabled{opacity:.6;cursor:not-allowed;transform:none}
        .alert-error{background:#fef2f2;color:#dc2626;border:1px solid #fecaca;padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.9rem}
        .back-link{text-align:center;margin-top:1rem;font-size:.9rem}
        .back-link a{color:#6366f1;font-weight:600;text-decoration:none}
        @media(max-width:480px){
            .card{padding:1.5rem 1.25rem;border-radius:16px}
        }
    </style>
</head>
<body>
<div class="card">
    <h1>{{ __('tenant::signup.otp_title') }}</h1>
    <p class="sub">{{ __('tenant::signup.otp_subtitle') }}</p>
    <div class="phone">{{ $phone }}</div>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('signup.otp.verify.submit') }}">
        @csrf
        <div class="form-group">
            <label>{{ __('tenant::signup.otp_code') }}</label>
            <input type="text" name="otp" placeholder="••••••" required maxlength="6" pattern="[0-9]{6}" autocomplete="one-time-code">
        </div>
        <div class="form-group">
            <label>{{ __('tenant::signup.name') }}</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('tenant::signup.name_placeholder') }}" required style="text-align:right;letter-spacing:normal;font-size:.95rem;direction:rtl">
        </div>
        <button type="submit" class="btn">{{ __('tenant::signup.otp_verify') }}</button>
    </form>

    <div class="back-link">
        <a href="{{ route('signup.show') }}">{{ __('tenant::signup.back') }}</a>
    </div>
</div>
</body>
</html>
