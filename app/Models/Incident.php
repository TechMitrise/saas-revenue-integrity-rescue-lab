<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'workspace_id', 'lab_run_id', 'account_id', 'subscription_id', 'invoice_id', 'fingerprint', 'type', 'severity',
    'status', 'title', 'reason', 'root_cause', 'recommended_action', 'revenue_impact', 'currency', 'assignee',
    'evidence', 'detected_at', 'last_detected_at', 'sla_breached_at', 'recovered_at',
])]
class Incident extends Model
{
    protected function casts(): array
    {
        return [
            'revenue_impact' => 'decimal:2',
            'evidence' => 'array',
            'detected_at' => 'datetime',
            'last_detected_at' => 'datetime',
            'sla_breached_at' => 'datetime',
            'recovered_at' => 'datetime',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(IncidentNote::class)->latest();
    }
}
