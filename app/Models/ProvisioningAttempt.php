<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'workspace_id', 'lab_run_id', 'entitlement_id', 'webhook_event_id', 'job_uuid',
    'attempt_number', 'status', 'failure_code', 'failure_message', 'duration_ms',
    'started_at', 'completed_at',
])]
class ProvisioningAttempt extends Model
{
    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function entitlement(): BelongsTo
    {
        return $this->belongsTo(Entitlement::class);
    }
}
