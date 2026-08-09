<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class XRayAccessController extends Controller
{
    public function create(): Response
    {
        abort_unless(config('rescue_lab.xray.enabled'), 404);

        return response()
            ->view('xray.access', [
                'ctaUrl' => config('rescue_lab.xray.cta_url'),
                'specimenRoute' => route('xray.sample'),
                'enabled' => config('rescue_lab.xray.enabled'),
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(config('rescue_lab.xray.enabled'), 404);

        $request->validate(['access_code' => ['required', 'string', 'max:120']]);
        $configured = (string) config('rescue_lab.xray.access_code');

        if ($configured === '' || ! hash_equals($configured, (string) $request->input('access_code'))) {
            return back()->withErrors(['access_code' => 'The access code was not accepted.'])->withInput();
        }

        $request->session()->put(config('rescue_lab.xray.session_key'), true);
        $request->session()->regenerate();

        return redirect()->route('xray.sample');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget(config('rescue_lab.xray.session_key'));

        return redirect()->route('xray.access');
    }
}
