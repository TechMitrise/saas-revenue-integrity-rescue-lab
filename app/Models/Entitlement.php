<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'workspace_id', 'lab_run_id', 'account_id', 'subscription_id', 'payment_id',
    'entitlement_key', 'state', 'requested_at', 'provisioned_at',
])]
class Entitlement extends Model
{
    protected function casts(): array
    {
        return ['requested_at' => 'datetime', 'provisioned_at' => 'datetime'];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ProvisioningAttempt::class);
    }
}
