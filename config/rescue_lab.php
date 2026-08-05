<?php

return [
    'provider' => 'synthetic-pay',
    'webhook_secret' => env('RESCUE_LAB_WEBHOOK_SECRET', 'synthetic-rescue-lab-signing-key'),
    'entitlement_sla_seconds' => (int) env('RESCUE_LAB_ENTITLEMENT_SLA_SECONDS', 120),
    'provisioning_max_attempts' => 3,
    'provisioning_backoff_seconds' => [1, 5, 15],
];
