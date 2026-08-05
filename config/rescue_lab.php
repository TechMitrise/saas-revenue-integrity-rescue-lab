<?php

return [
    'provider' => 'synthetic-pay',
    'webhook_secret' => env('RESCUE_LAB_WEBHOOK_SECRET', 'synthetic-rescue-lab-signing-key'),
    'entitlement_sla_seconds' => (int) env('RESCUE_LAB_ENTITLEMENT_SLA_SECONDS', 120),
    'provisioning_max_attempts' => 3,
    'provisioning_backoff_seconds' => [1, 5, 15],
    'repository_url' => env('RESCUE_LAB_REPOSITORY_URL'),
    'demo_mutations_enabled' => filter_var(
        env('RESCUE_LAB_DEMO_MUTATIONS_ENABLED', in_array(env('APP_ENV', 'production'), ['local', 'testing'], true)),
        FILTER_VALIDATE_BOOL,
    ),
];
