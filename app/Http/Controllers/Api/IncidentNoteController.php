<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncidentNoteController extends Controller
{
    public function store(Request $request, int $incident, DashboardService $dashboard): JsonResponse
    {
        $validated = $request->validate(['body' => ['required', 'string', 'max:1000']]);
        $user = $request->user();
        $record = Incident::query()
            ->where('workspace_id', $user->workspace_id)
            ->findOrFail($incident);

        $record->notes()->create([
            'user_id' => $user->id,
            'author_name' => $user->name,
            'body' => trim($validated['body']),
        ]);

        return response()->json([
            'incident' => $dashboard->serializeIncident($record->load(['account', 'notes'])),
        ], 201);
    }
}
