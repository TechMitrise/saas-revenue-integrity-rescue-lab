<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Services\RevenueIntegrityAuditor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function store(Request $request, RevenueIntegrityAuditor $auditor, DashboardService $dashboard): JsonResponse
    {
        $user = $request->user();
        $result = $auditor->run($user->workspace, $user);

        return response()->json([
            'summary' => [
                'detected' => $result['detected'],
                'created' => $result['created'],
                'refreshed' => $result['refreshed'],
            ],
            'dashboard' => $dashboard->for($user->workspace),
        ]);
    }
}
