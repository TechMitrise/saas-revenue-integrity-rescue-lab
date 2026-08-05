<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Database\Seeders\DemoScenarioSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DemoResetController extends Controller
{
    public function __invoke(
        Request $request,
        DemoScenarioSeeder $seeder,
        DashboardService $dashboard,
    ): JsonResponse {
        abort_unless(app()->environment(['local', 'testing']), 404);

        $user = $request->user();
        $seeder->seed($user->workspace);

        return response()->json(['dashboard' => $dashboard->for($user->workspace)]);
    }
}
