<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\AuditRun;
use App\Models\Incident;
use App\Models\Subscription;
use App\Models\Workspace;
use Illuminate\Support\Collection;

class DashboardService
{
    /** @return array<string, mixed> */
    public function for(Workspace $workspace): array
    {
        $subscriptions = Subscription::query()->whereBelongsTo($workspace)->get();
        $activeSubscriptions = $subscriptions->where('status', 'active');
        $incidents = Incident::query()
            ->whereBelongsTo($workspace)
            ->with(['account', 'notes'])
            ->orderByRaw("FIELD(status, 'open', 'investigating', 'recovered', 'dismissed')")
            ->orderByRaw("FIELD(severity, 'critical', 'high', 'medium', 'low')")
            ->orderByDesc('revenue_impact')
            ->get();
        $activeIncidents = $incidents->whereIn('status', ['open', 'investigating']);
        $affectedSubscriptions = $activeIncidents->pluck('subscription_id')->filter()->unique();
        $healthyCount = $activeSubscriptions->whereNotIn('id', $affectedSubscriptions)->count();

        $breakdown = $activeIncidents
            ->groupBy('type')
            ->map(fn (Collection $group, string $type): array => [
                'type' => $type,
                'count' => $group->count(),
                'impact' => (float) $group->sum('revenue_impact'),
            ])
            ->sortByDesc('impact')
            ->values();

        $latestAudit = AuditRun::query()->whereBelongsTo($workspace)->latest('completed_at')->first();

        return [
            'workspace' => ['name' => $workspace->name, 'currency' => $workspace->currency],
            'metrics' => [
                'totalMrr' => (float) $activeSubscriptions->sum('expected_monthly_amount'),
                'atRisk' => (float) $activeIncidents->sum('revenue_impact'),
                'recovered' => (float) $incidents->where('status', 'recovered')->sum('revenue_impact'),
                'activeIncidents' => $activeIncidents->count(),
                'healthyRate' => $activeSubscriptions->isEmpty() ? 100 : (int) round(($healthyCount / $activeSubscriptions->count()) * 100),
                'monitoredAccounts' => $workspace->accounts()->count(),
            ],
            'incidents' => $incidents->map($this->serializeIncident(...))->values(),
            'breakdown' => $breakdown,
            'activity' => Activity::query()
                ->where('workspace_id', $workspace->id)
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn (Activity $activity): array => [
                    'id' => $activity->id,
                    'type' => $activity->type,
                    'message' => $activity->message,
                    'at' => $activity->created_at->toIso8601String(),
                ]),
            'lastAuditAt' => $latestAudit?->completed_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    public function serializeIncident(Incident $incident): array
    {
        return [
            'id' => $incident->id,
            'accountName' => $incident->account->name,
            'type' => $incident->type,
            'severity' => $incident->severity,
            'status' => $incident->status,
            'title' => $incident->title,
            'reason' => $incident->reason,
            'recommendedAction' => $incident->recommended_action,
            'revenueImpact' => (float) $incident->revenue_impact,
            'currency' => $incident->currency,
            'assignee' => $incident->assignee,
            'evidence' => $incident->evidence,
            'createdAt' => $incident->created_at->toIso8601String(),
            'lastDetectedAt' => $incident->last_detected_at->toIso8601String(),
            'recoveredAt' => $incident->recovered_at?->toIso8601String(),
            'notes' => $incident->notes->map(fn ($note): array => [
                'id' => $note->id,
                'author' => $note->author_name,
                'body' => $note->body,
                'at' => $note->created_at->toIso8601String(),
            ])->values(),
        ];
    }
}
