<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['workspace_id', 'account_id', 'external_id', 'plan', 'status', 'expected_monthly_amount', 'currency', 'started_at', 'canceled_at'])]
class Subscription extends Model
{
    protected function casts(): array
    {
        return [
            'expected_monthly_amount' => 'decimal:2',
            'started_at' => 'date',
            'canceled_at' => 'date',
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

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
