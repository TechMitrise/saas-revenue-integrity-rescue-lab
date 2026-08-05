<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#12231f">
    <title>Sign in | Rescue Lab</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-story">
            <a class="brand brand-light" href="{{ route('login') }}"><span class="brand-mark">RI</span><span><strong>Rescue Lab</strong><small>SaaS revenue integrity</small></span></a>
            <div class="login-message"><p class="eyebrow">Synthetic paid-access incident</p><h1>Cash arrived.<br>Access did not.</h1><p>Trace one payment through duplicate webhook delivery, a failed entitlement job, SLA breach, safe recovery, and stabilised replay.</p></div>
            <p class="local-note">Local lab / Synthetic evidence / No external connections</p>
        </section>
        <section class="login-form-panel">
            <form class="login-form" method="POST" action="{{ route('login') }}">
                @csrf
                <div><p class="eyebrow">Rescue Control Room</p><h2>Enter the lab.</h2><p class="form-intro">The local demonstration identity is clearly labelled and prefilled.</p></div>
                @if ($errors->any())<p class="form-error">{{ $errors->first() }}</p>@endif
                <label><span>Email address</span><input name="email" type="email" value="{{ old('email', app()->environment('local') ? 'operator@rescuelab.test' : '') }}" autocomplete="email" required autofocus></label>
                <label><span>Password</span><input name="password" type="password" value="{{ app()->environment('local') ? 'rescue-demo' : '' }}" autocomplete="current-password" required></label>
                <label class="remember-row"><input name="remember" type="checkbox" value="1"><span>Keep this local session active</span></label>
                <button class="primary-button" type="submit">Open the Control Room <span aria-hidden="true">&#8599;</span></button>
                @env('local')<p class="demo-hint">Synthetic local demo credentials. Never use them in production.</p>@endenv
            </form>
        </section>
    </main>
</body>
</html>
