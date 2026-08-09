# Synthetic Scenario Study

> Payment succeeded. Access failed. The system traced, recovered and stabilised the revenue path safely.

This is a synthetic Engineering Lab scenario using fictional organisations, deterministic failures and local-only credentials. It is not a client case study, testimonial, production incident report or claim of recovered customer revenue.

## Business Context

Northstar Labs is the fictional customer account in the Rescue Lab fixture. It has a USD 4,800 monthly subscription for `northstar-paid-access-v1`. The business expectation is simple: after the payment succeeds, subscription access should become active within the configured entitlement SLA.

## Failure And Consequence

The synthetic payment succeeds and the subscription becomes active, but the customer remains locked out because the entitlement job exhausts three bounded attempts. The lab records one paid-but-locked incident, one affected synthetic account and USD 4,800 of synthetic monthly exposure.

Commercially, the demonstrated failure is a paid customer receiving no value after payment. The Lab uses that state to show how a revenue path can become a reliability, support and churn-risk problem even when cash collection is healthy.

## Evidence Inspected

| Evidence | Lab source |
| --- | --- |
| Successful payment and active subscription | `payments`, `subscriptions`, Control Room |
| Ten signed webhook deliveries | `webhook_deliveries`, Customer Incident timeline |
| One provider event and one business effect | `webhook_events`, Root Cause |
| Three failed provisioning attempts | `provisioning_attempts`, Root Cause |
| Locked entitlement after payment | `entitlements`, Control Room and Safe Recovery |
| SLA breach and revenue exposure | `incidents`, `RevenueIntegrityAuditor` |
| Repaired action and repeat no-op | `reconciliation_actions`, Safe Recovery |

## Demonstrated Diagnosis

The root cause supported by the Lab evidence is not payment failure. Cash collection is healthy. The failure is the fulfilment path after payment: duplicate provider delivery is handled as at-least-once input, but the entitlement provisioning job times out three times and no automatic recovery closes the locked-access gap before the SLA breach.

## Intervention

`ReconciliationService` repairs the existing entitlement under a database transaction and row locks. It moves access from `locked` to `active`, records before/after state, marks the incident recovered and stores one immutable `repaired` action with one business effect.

## Safety Controls

- HMAC verification for the synthetic provider contract.
- Persistent webhook delivery ledger and provider-event inbox.
- Unique provider event and entitlement constraints.
- Guarded subscription transition and idempotent business effect.
- Three bounded provisioning attempts with retained failure evidence.
- Workspace-scoped reads and mutations.
- Lock-protected reconciliation that updates the existing entitlement.
- Repeat reconciliation recorded as `no_op` with zero business effects.

## Verified Lab Result

The finished scenario demonstrates:

1. Payment succeeds.
2. Access remains locked.
3. Payment, webhook, queue, entitlement and incident evidence share a visible correlation trail.
4. Reconciliation restores access once.
5. A second reconciliation creates no duplicate entitlement and zero additional business effects.
6. `lab:reset` restores the paid-but-locked state so the scenario can be replayed deterministically.

## Code And Test References

- `app/Services/WebhookIngestor.php`
- `app/Jobs/ProvisionEntitlement.php`
- `app/Services/EntitlementProvisioner.php`
- `app/Services/RevenueIntegrityAuditor.php`
- `app/Services/ReconciliationService.php`
- `database/seeders/DemoScenarioSeeder.php`
- `database/migrations/2026_08_05_000200_create_paid_access_rescue_tables.php`
- `tests/Feature/PaidAccessRescueTest.php`
- `tests/Unit/RevenueIntegrityAuditorTest.php`
- `tests/Browser/revenue-rescue.spec.js`

