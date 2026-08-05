<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'workspace_id', 'account_id', 'subscription_id', 'payment_id', 'scenario_key', 'mode',
    'correlation_id', 'status', 'webhook_deliveries', 'business_actions', 'queue_attempts',
    'queue_failures', 'entitlement_state', 'payment_to_access_ms', 'sla_threshold_ms',
    'sla_breached', 'reconciliation_result', 'before_state', 'after_state', 'query_count',
    'processing_ms', 'evidence', 'started_at', 'completed_at',
])]
class LabRun extends Model
{
    protected function casts(): array
    {
        return [
            'sla_breached' => 'boolean',
            'before_state' => 'array',
            'after_state' => 'array',
            'evidence' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(WebhookEvent::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ProvisioningAttempt::class);
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(Entitlement::class);
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(ReconciliationAction::class);
    }
}
