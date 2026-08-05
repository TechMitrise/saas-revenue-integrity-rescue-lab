<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f3eddf">
    <title>Sign in | Revenue Integrity Rescue Lab</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-story">
            <a class="brand brand-light" href="{{ route('login') }}">
                <span class="brand-mark">RI</span>
                <span><strong>Rescue Lab</strong><small>Revenue integrity desk</small></span>
            </a>
            <div class="login-message">
                <p class="eyebrow">Revenue operations, focused</p>
                <h1>Every leak leaves a trail.</h1>
                <p>Trace failed payments, billing gaps, and contract drift before they become a forecast surprise.</p>
            </div>
            <div class="orbit-graphic" aria-hidden="true"><i></i><i></i><i></i><span>12.2k</span></div>
            <p class="local-note">Local workspace / No external connections</p>
        </section>

        <section class="login-form-panel">
            <form class="login-form" method="POST" action="{{ route('login') }}">
                @csrf
                <div>
                    <p class="eyebrow">Secure workspace</p>
                    <h2>Welcome back.</h2>
                    <p class="form-intro">Sign in to continue the August revenue review.</p>
                </div>

                @if ($errors->any())
                    <div class="form-error" role="alert">{{ $errors->first() }}</div>
                @endif

                <label>
                    <span>Email address</span>
                    <input name="email" type="email" value="{{ old('email', app()->environment('local') ? 'operator@rescuelab.test' : '') }}" autocomplete="email" required autofocus>
                </label>
                <label>
                    <span>Password</span>
                    <input name="password" type="password" value="{{ app()->environment('local') ? 'rescue-demo' : '' }}" autocomplete="current-password" required>
                </label>
                <label class="remember-row">
                    <input name="remember" type="checkbox" value="1">
                    <span>Keep me signed in on this device</span>
                </label>
                <button class="primary-button" type="submit">Enter the rescue desk <span aria-hidden="true">&#8599;</span></button>
                @env('local')<p class="demo-hint">Demo access is prefilled for this local environment.</p>@endenv
            </form>
        </section>
    </main>
</body>
</html>
