<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardDataController extends Controller
{
    public function __invoke(Request $request, DashboardService $dashboard): JsonResponse
    {
        return response()->json($dashboard->for($request->user()->workspace));
    }
}
