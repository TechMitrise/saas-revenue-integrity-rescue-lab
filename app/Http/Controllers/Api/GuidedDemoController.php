<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Services\GuidedDemoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuidedDemoController extends Controller
{
    public function start(Request $request, GuidedDemoService $guide, DashboardService $dashboard): JsonResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        $user = $request->user();
        $guide->start($user->workspace);

        return response()->json([
            'message' => 'Successful synthetic payment recorded.',
            'focus' => 'control',
            'dashboard' => $dashboard->for($user->workspace),
        ]);
    }

    public function advance(Request $request, GuidedDemoService $guide, DashboardService $dashboard): JsonResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        $user = $request->user();
        $result = $guide->advance($user->workspace, $user);

        return response()->json([
            ...$result,
            'dashboard' => $dashboard->for($user->workspace),
        ]);
    }
}
