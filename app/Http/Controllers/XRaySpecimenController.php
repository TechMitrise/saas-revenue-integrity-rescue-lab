<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Services\XRayReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class XRaySpecimenController extends Controller
{
    public function __invoke(Request $request, XRayReportService $reports): Response|RedirectResponse
    {
        abort_unless(config('rescue_lab.xray.enabled'), 404);

        if ($request->session()->get(config('rescue_lab.xray.session_key')) !== true) {
            return redirect()->route('xray.access');
        }

        $workspace = Workspace::query()->where('slug', 'rescue-lab')->firstOrFail();
        $report = $reports->report($workspace);

        return response()
            ->view('xray.sample', ['report' => $report])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
