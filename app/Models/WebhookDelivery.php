<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'workspace_id', 'lab_run_id', 'webhook_event_id', 'provider', 'provider_event_id',
    'provider_delivery_id', 'signature_valid', 'duplicate_event', 'payload_hash',
    'response_code', 'received_at',
])]
class WebhookDelivery extends Model
{
    protected function casts(): array
    {
        return ['signature_valid' => 'boolean', 'duplicate_event' => 'boolean', 'received_at' => 'datetime'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(WebhookEvent::class, 'webhook_event_id');
    }
}
