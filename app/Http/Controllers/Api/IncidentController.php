<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Incident;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IncidentController extends Controller
{
    public function update(Request $request, int $incident, DashboardService $dashboard): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', Rule::in(['open', 'investigating', 'recovered', 'dismissed'])],
            'assignee' => ['sometimes', 'nullable', 'string', 'max:80'],
        ]);
        $user = $request->user();

        $record = DB::transaction(function () use ($incident, $validated, $user): Incident {
            $record = Incident::query()
                ->where('workspace_id', $user->workspace_id)
                ->findOrFail($incident);

            if (isset($validated['status']) && $validated['status'] !== $record->status) {
                $record->status = $validated['status'];
                $record->recovered_at = $record->status === 'recovered' ? now() : null;

                Activity::query()->create([
                    'workspace_id' => $user->workspace_id,
                    'user_id' => $user->id,
                    'type' => 'incident_updated',
                    'message' => sprintf('%s: %s moved to %s.', $record->account->name, $record->title, $record->status),
                    'context' => ['incident_id' => $record->id],
                ]);
            }

            if (array_key_exists('assignee', $validated)) {
                $record->assignee = filled($validated['assignee']) ? trim($validated['assignee']) : null;
            }

            $record->save();

            return $record->load(['account', 'notes']);
        });

        return response()->json(['incident' => $dashboard->serializeIncident($record)]);
    }

    public function export(Request $request): StreamedResponse
    {
        $incidents = Incident::query()
            ->where('workspace_id', $request->user()->workspace_id)
            ->with('account')
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($incidents): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['id', 'account', 'type', 'severity', 'status', 'revenue_impact', 'currency', 'assignee', 'created_at']);

            foreach ($incidents as $incident) {
                fputcsv($output, [
                    $incident->id,
                    $incident->account->name,
                    $incident->type,
                    $incident->severity,
                    $incident->status,
                    $incident->revenue_impact,
                    $incident->currency,
                    $incident->assignee,
                    $incident->created_at->toIso8601String(),
                ]);
            }

            fclose($output);
        }, 'revenue-integrity-incidents.csv', ['Content-Type' => 'text/csv']);
    }
}
