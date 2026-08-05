<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'workspace_id', 'lab_run_id', 'incident_id', 'entitlement_id', 'requested_by',
    'action_id', 'result', 'business_effects', 'before_state', 'after_state', 'reason', 'created_at',
])]
class ReconciliationAction extends Model
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['before_state' => 'array', 'after_state' => 'array', 'created_at' => 'datetime'];
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function entitlement(): BelongsTo
    {
        return $this->belongsTo(Entitlement::class);
    }
}
