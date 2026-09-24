@php $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr'; @endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $dir }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.admin_login_title') }} — LashInk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            background: #f1f5f9;
        }

        /* ── Left Panel ── */
        .l-panel {
            flex: 0 0 45%;
            background: linear-gradient(145deg, #0a0a0a 0%, #3a1a25 50%, #a94a67 100%);
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        /* Decorative circles */
        .l-panel::before {
            content: '';
            position: absolute;
            top: -120px; right: -80px;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
        }
        .l-panel::after {
            content: '';
            position: absolute;
            bottom: -100px; left: -60px;
            width: 320px; height: 320px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
        }

        .l-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            z-index: 1;
        }
        .l-brand-icon {
            width: 44px; height: 44px;
            background: rgba(255,255,255,.15);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; color: #fff;
            border: 1px solid rgba(255,255,255,.2);
        }
        .l-brand-name {
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -.02em;
        }
        .l-brand-name span { color: #e59ab0; }
        .l-brand-logo { width: 210px; height: auto; }

        .l-hero { position: relative; z-index: 1; }

        .l-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 20px;
            padding: 5px 14px;
            font-size: .75rem;
            font-weight: 600;
            color: #f3c6d2;
            margin-bottom: 20px;
        }

        .l-title {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
            letter-spacing: -.03em;
            margin-bottom: 12px;
        }
        .l-title span { color: #e59ab0; }

        .l-subtitle {
            font-size: .9rem;
            color: rgba(255,255,255,.65);
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .l-features { list-style: none; display: flex; flex-direction: column; gap: 14px; }
        .l-features li {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,.85);
            font-size: .875rem;
            font-weight: 500;
        }
        .l-features li .feat-icon {
            width: 32px; height: 32px;
            background: rgba(255,255,255,.1);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: .85rem;
            flex-shrink: 0;
        }

        .l-stats {
            display: flex;
            gap: 24px;
            position: relative;
            z-index: 1;
        }
        .l-stat {
            display: flex;
            flex-direction: column;
        }
        .l-stat-val {
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.03em;
        }
        .l-stat-lbl {
            font-size: .72rem;
            color: rgba(255,255,255,.55);
            font-weight: 500;
        }
        .l-stat-divider {
            width: 1px;
            background: rgba(255,255,255,.15);
            margin: 4px 0;
        }

        /* ── Right Panel ── */
        .r-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
        }

        .r-wrap {
            width: 100%;
            max-width: 400px;
        }

        .r-header { margin-bottom: 32px; }
        .r-eyebrow {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .78rem;
            font-weight: 600;
            color: #cb6984;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: 10px;
        }
        .r-eyebrow .dot {
            width: 6px; height: 6px;
            background: #cb6984;
            border-radius: 50%;
        }
        .r-title { font-size: 1.6rem; font-weight: 800; color: #171717; letter-spacing: -.03em; }
        .r-sub   { font-size: .855rem; color: #64748b; margin-top: 6px; }

        /* Alert */
        .alert-err {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: .845rem;
            color: #dc2626;
            font-weight: 500;
        }

        /* Form */
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: .82rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 7px;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: .95rem;
            pointer-events: none;
        }
        .form-input {
            width: 100%;
            padding: 11px 14px 11px 42px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: .875rem;
            font-family: inherit;
            color: #111827;
            background: #fff;
            transition: all .2s ease;
            outline: none;
        }
        .form-input::placeholder { color: #9ca3af; }
        .form-input:focus {
            border-color: #cb6984;
            box-shadow: 0 0 0 3px rgba(203,105,132,.15);
        }
        .form-input.is-invalid { border-color: #ef4444; }
        .form-input.is-invalid:focus { box-shadow: 0 0 0 3px rgba(239,68,68,.12); }

        /* Password toggle */
        .pw-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            font-size: .95rem;
            padding: 2px;
        }
        .pw-toggle:hover { color: #cb6984; }

        .invalid-feedback { font-size: .78rem; color: #ef4444; margin-top: 5px; display: block; }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }
        .form-check input[type="checkbox"] {
            width: 16px; height: 16px;
            border-radius: 4px;
            accent-color: #cb6984;
            cursor: pointer;
        }
        .form-check label {
            font-size: .845rem;
            color: #6b7280;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: #cb6984;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: .9rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all .2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            letter-spacing: -.01em;
        }
        .btn-login:hover {
            background: #a94a67;
            box-shadow: 0 4px 14px rgba(203,105,132,.4);
            transform: translateY(-1px);
        }
        .btn-login:active { transform: translateY(0); box-shadow: none; }

        .r-footer {
            margin-top: 32px;
            text-align: center;
            font-size: .78rem;
            color: #9ca3af;
        }
        .r-footer a { color: #cb6984; font-weight: 500; text-decoration: none; }

        /* Responsive */
        @media (max-width: 900px) { .l-panel { flex: 0 0 40%; padding: 36px 32px; } }
        @media (max-width: 768px) {
            body { display: block; }
            .l-panel { display: none; }
            .r-panel { min-height: 100vh; padding: 32px 20px; }
        }

        @if($dir === 'rtl')
        .input-icon { left: auto; right: 14px; }
        .form-input { padding: 11px 42px 11px 14px; }
        .pw-toggle { right: auto; left: 14px; }
        @endif
    </style>
</head>
<body>

{{-- LEFT PANEL --}}
<div class="l-panel">

    <div class="l-brand">
        <img src="{{ asset('assets/admin/img/logo-light.png') }}" alt="LashInk" class="l-brand-logo">
    </div>

    <div class="l-hero">
        <div class="l-badge">
            <i class="bi bi-shield-check-fill"></i> {{ __('messages.admin_portal') }}
        </div>
        <h1 class="l-title">{{ __('messages.manage_platform_title') }}<br><span>{{ __('messages.manage_platform_subtitle') }}</span></h1>
        <p class="l-subtitle">
            {{ __('messages.manage_platform_desc') }}
        </p>
        <ul class="l-features">
            <li>
                <div class="feat-icon"><i class="bi bi-calendar2-week-fill"></i></div>
                {{ __('messages.feature_appointments') }}
            </li>
            <li>
                <div class="feat-icon"><i class="bi bi-receipt"></i></div>
                {{ __('messages.feature_accounting') }}
            </li>
            <li>
                <div class="feat-icon"><i class="bi bi-box-seam-fill"></i></div>
                {{ __('messages.feature_inventory') }}
            </li>
            <li>
                <div class="feat-icon"><i class="bi bi-people-fill"></i></div>
                {{ __('messages.feature_payroll') }}
            </li>
        </ul>
    </div>

</div>

{{-- RIGHT PANEL --}}
<div class="r-panel">
    <div class="r-wrap">

        <div class="r-header">
            <div class="r-eyebrow">
                <span class="dot"></span> {{ __('messages.secure_access') }}
            </div>
            <h2 class="r-title">{{ __('messages.welcome_back_short') }}</h2>
            <p class="r-sub">{{ __('messages.sign_in_admin_desc') }}</p>
        </div>

        {{-- Error Message --}}
        @if($errors->any() || session('error'))
        <div class="alert-err">
            <i class="bi bi-exclamation-circle-fill"></i>
            {{ $errors->first() ?: session('error') }}
        </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}" autocomplete="off">
            @csrf

            {{-- Username --}}
            <div class="form-group">
                <label class="form-label" for="username">{{ __('messages.username_label') }}</label>
                <div class="input-wrap">
                    <i class="input-icon bi bi-person"></i>
                    <input
                        id="username"
                        name="username"
                        type="text"
                        class="form-input {{ $errors->has('username') ? 'is-invalid' : '' }}"
                        placeholder="{{ __('messages.username_ph') }}"
                        value="{{ old('username') }}"
                        required
                        autofocus
                    >
                </div>
                @error('username')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label class="form-label" for="password">{{ __('messages.password_label') }}</label>
                <div class="input-wrap">
                    <i class="input-icon bi bi-lock"></i>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                        placeholder="{{ __('messages.password_ph') }}"
                        required
                    >
                    <button type="button" class="pw-toggle" id="pwToggle" aria-label="Toggle password">
                        <i class="bi bi-eye" id="pwIcon"></i>
                    </button>
                </div>
                @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Remember Me --}}
            <div class="form-check">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">{{ __('messages.keep_signed_in') }}</label>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right"></i> {{ __('messages.sign_in_admin_btn') }}
            </button>
        </form>

        <div class="r-footer">
            &copy; {{ date('Y') }} LashInk. {{ __('messages.all_rights_reserved') }}
        </div>

    </div>
</div>

<script>
    const pwToggle = document.getElementById('pwToggle');
    const pwInput  = document.getElementById('password');
    const pwIcon   = document.getElementById('pwIcon');

    pwToggle.addEventListener('click', () => {
        const isPassword = pwInput.type === 'password';
        pwInput.type = isPassword ? 'text' : 'password';
        pwIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
</script>
</body>
</html>
