<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Services\DashboardService;
use App\Services\ReconciliationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReconciliationController extends Controller
{
    public function store(
        Request $request,
        int $incident,
        ReconciliationService $reconciliation,
        DashboardService $dashboard,
    ): JsonResponse {
        $user = $request->user();
        $record = Incident::query()
            ->where('workspace_id', $user->workspace_id)
            ->where('type', 'paid_but_locked')
            ->findOrFail($incident);
        $action = $reconciliation->reconcile($record, $user);

        return response()->json([
            'action' => [
                'id' => $action->action_id,
                'result' => $action->result,
                'businessEffects' => $action->business_effects,
                'before' => $action->before_state,
                'after' => $action->after_state,
            ],
            'dashboard' => $dashboard->for($user->workspace),
        ]);
    }
}
