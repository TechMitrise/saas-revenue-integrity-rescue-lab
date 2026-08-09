<?php

return [
    'provider' => 'synthetic-pay',
    'webhook_secret' => env('RESCUE_LAB_WEBHOOK_SECRET', 'synthetic-rescue-lab-signing-key'),
    'entitlement_sla_seconds' => (int) env('RESCUE_LAB_ENTITLEMENT_SLA_SECONDS', 120),
    'provisioning_max_attempts' => 3,
    'provisioning_backoff_seconds' => [1, 5, 15],
    'repository_url' => env('RESCUE_LAB_REPOSITORY_URL'),
    'contact_url' => env('RESCUE_LAB_CONTACT_URL'),
    'video_url' => env('RESCUE_LAB_VIDEO_URL'),
    'xray' => [
        'enabled' => filter_var(env('CRITICAL_XRAY_SPECIMEN_ENABLED', false), FILTER_VALIDATE_BOOL),
        'access_code' => env('CRITICAL_XRAY_ACCESS_CODE'),
        'session_key' => 'critical_xray_specimen_unlocked',
        'cta_url' => env('TRIAGE_CTA_URL') ?: env('RESCUE_LAB_CONTACT_URL'),
    ],
    'demo_mutations_enabled' => filter_var(
        env('RESCUE_LAB_DEMO_MUTATIONS_ENABLED', in_array(env('APP_ENV', 'production'), ['local', 'testing'], true)),
        FILTER_VALIDATE_BOOL,
    ),
];
