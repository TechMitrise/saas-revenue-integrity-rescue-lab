<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#12231f">
    <title>Protected Critical Software X-Ray Specimen</title>
    @vite('resources/css/xray.css')
</head>
<body class="xray-access-page">
    <main class="access-shell">
        <section class="access-copy">
            <p class="xray-eyebrow">Protected buyer specimen</p>
            <h1>Critical Software X-Ray</h1>
            <p>This synthetic professional-service specimen shows what a buyer receives from a paid Critical Software X-Ray. It is not a client case study, production monitoring product or public demo.</p>
            <ul>
                <li>Evidence-backed paid-but-locked Rescue Lab incident.</li>
                <li>Observed facts separated from inference, risk and recommendation.</li>
                <li>No real customer, credential or production claim is included.</li>
            </ul>
        </section>

        <form class="access-form" method="POST" action="{{ route('xray.access.store') }}">
            @csrf
            <p class="xray-eyebrow">Access required</p>
            <h2>Enter specimen code.</h2>
            <p>Access is server-side and session-based. Attempts are rate limited.</p>
            @error('access_code')
                <div class="access-error">{{ $message }}</div>
            @enderror
            <label>
                <span>Access code</span>
                <input name="access_code" type="password" autocomplete="current-password" required autofocus>
            </label>
            <button type="submit">Unlock specimen</button>
            <small>Use the private specimen code supplied out of band. No access code is stored in this page.</small>
        </form>
    </main>
</body>
</html>
