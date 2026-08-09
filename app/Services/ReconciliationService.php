<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Entitlement;
use App\Models\Incident;
use App\Models\LabRun;
use App\Models\ReconciliationAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReconciliationService
{
    public function reconcile(Incident $incident, ?User $user = null): ReconciliationAction
    {
        return DB::transaction(function () use ($incident, $user): ReconciliationAction {
            $incident = Incident::query()->whereKey($incident->id)->lockForUpdate()->firstOrFail();
            $run = LabRun::query()->whereKey($incident->lab_run_id)->lockForUpdate()->firstOrFail();
            $entitlement = Entitlement::query()
                ->where('workspace_id', $incident->workspace_id)
                ->where('subscription_id', $incident->subscription_id)
                ->lockForUpdate()
                ->firstOrFail();
            $before = $this->snapshot($incident, $entitlement, $run);
            $businessEffects = 0;

            if ($entitlement->state === 'active') {
                $result = 'no_op';
                $reason = 'Access was already active; unique entitlement and locked transaction prevented a second business effect.';
            } else {
                $entitlement->update(['state' => 'active', 'provisioned_at' => now()]);
                $incident->update(['status' => 'recovered', 'recovered_at' => now()]);
                $run->update(['entitlement_state' => 'active', 'reconciliation_result' => 'repaired', 'status' => 'completed']);
                $businessEffects = 1;
                $result = 'repaired';
                $reason = 'Locked reconciliation activated the existing entitlement without creating a duplicate.';
            }

            $after = $this->snapshot($incident->fresh(), $entitlement->fresh(), $run->fresh(), $businessEffects);
            $action = ReconciliationAction::query()->create([
                'workspace_id' => $incident->workspace_id,
                'lab_run_id' => $run->id,
                'incident_id' => $incident->id,
                'entitlement_id' => $entitlement->id,
                'requested_by' => $user?->id,
                'action_id' => (string) Str::uuid(),
                'result' => $result,
                'business_effects' => $businessEffects,
                'before_state' => $before,
                'after_state' => $after,
                'reason' => $reason,
                'created_at' => now(),
            ]);

            if ($result === 'repaired') {
                $run->update(['before_state' => $before, 'after_state' => $after]);
            }

            Activity::query()->create([
                'workspace_id' => $incident->workspace_id,
                'user_id' => $user?->id,
                'type' => 'reconciliation_completed',
                'message' => "Reconciliation {$result}: {$businessEffects} business effect(s).",
                'context' => ['action_id' => $action->action_id, 'incident_id' => $incident->id],
            ]);

            return $action;
        });
    }

    /** @return array<string, mixed> */
    private function snapshot(Incident $incident, Entitlement $entitlement, LabRun $run, int $pendingBusinessEffects = 0): array
    {
        return [
            'incident_status' => $incident->status,
            'entitlement_id' => $entitlement->id,
            'entitlement_state' => $entitlement->state,
            'entitlement_count' => Entitlement::query()
                ->where('workspace_id', $incident->workspace_id)
                ->where('subscription_id', $incident->subscription_id)
                ->count(),
            'business_effects' => ReconciliationAction::query()
                ->where('lab_run_id', $run->id)
                ->sum('business_effects') + $pendingBusinessEffects,
        ];
    }
}
