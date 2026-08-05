# Architecture

## System shape

Rescue Lab is a tenant-aware Laravel monolith with MySQL persistence and a Valkey-backed Horizon queue.

```text
Synthetic provider
  -> POST /api/webhooks/synthetic-pay
  -> HMAC verification
  -> webhook_deliveries (every unique delivery)
  -> webhook_events (one row per provider event)
  -> idempotent consumer under a database transaction
  -> guarded subscription transition + unique locked entitlement
  -> ProvisionEntitlement job on Valkey/Horizon
  -> provisioning_attempts (bounded success/failure evidence)
  -> paid-but-locked detector
  -> Rescue Control Room
  -> lock-protected reconciliation
  -> immutable reconciliation_actions + active entitlement
```

The browser uses Laravel session authentication and CSRF protection. The webhook route is CSRF-exempt because it uses HMAC authentication; it accepts only the explicitly synthetic contract.

## Tenant boundary

`workspace_id` is present on every business and evidence table. Authenticated controllers derive it from the current user. Reconciliation queries the incident through that workspace and returns `404` across tenant boundaries. Provider records also carry workspace-scoped unique keys.

## Paid-access model

| Table | Role |
| --- | --- |
| `workspaces`, `users` | Tenant and authenticated operator |
| `accounts` | Synthetic customer organisation |
| `plans`, `subscriptions` | Commercial expectation and safe lifecycle state |
| `invoices`, `payments` | Successful synthetic payment evidence |
| `lab_runs` | Reproducible fragile/stabilised measurements |
| `webhook_deliveries` | One row per unique provider delivery ID |
| `webhook_events` | Persistent inbox, unique provider event, processing result |
| `entitlements` | Access state with one business entitlement per subscription/key |
| `provisioning_attempts` | Job UUID, attempt number, duration, and retained failure |
| `incidents` | Correlated paid-but-locked finding and revenue impact |
| `reconciliation_actions` | Immutable before/after audit and business-effect count |
| `activities`, `audit_runs` | Operator-visible audit history |

## Event and effect guarantees

Delivery remains at least once. Ten different delivery IDs may reference one provider event ID. The database constraint on `(workspace_id, provider, provider_event_id)` creates one inbox event. Only a newly accepted event enters the consumer.

The consumer locks the subscription, permits only `pending -> active` for a successful payment, and creates the entitlement through the unique `(workspace_id, subscription_id, entitlement_key)` key. Replayed or out-of-order events cannot create another entitlement or repeat the business transition.

This is an application-level effectively-once business effect, not distributed exactly-once delivery.

## Queue failure and SLA

`ProvisionEntitlement` is a real `ShouldQueue` job with three attempts, a 30-second timeout, and `[1, 5, 15]` second backoff. Compose runs Horizon against Valkey. The deterministic fragile adapter writes each failed attempt before throwing `SyntheticProvisioningFailure`; Horizon can retain broker-level failure visibility while the application table retains domain evidence.

The lab computes payment-to-access duration against `subscriptions.access_sla_seconds`. A successful payment plus active subscription, locked entitlement, and breached SLA produces the `paid_but_locked` incident. Revenue impact equals the active paid subscription's synthetic monthly amount.

## Reconciliation

`ReconciliationService` runs in a MySQL transaction and obtains row locks for the incident, lab run, and entitlement. It updates the existing entitlement instead of creating one. Every invocation writes an immutable reconciliation action:

- First run: `repaired`, one business effect, locked-to-active snapshot.
- Repeat run: `no_op`, zero business effects, entitlement count remains one.

## Fragile and stabilised runs

Both modes use `northstar-paid-access-v1`, USD 4,800 synthetic monthly revenue, and ten deliveries of one payment event.

- **Fragile:** three deterministic queue failures, locked access, payment-to-access SLA breach, paid-but-locked incident.
- **Stabilised:** verified signature, persistent inbox, idempotent consumer, unique entitlement, successful first job attempt, active access within SLA.

`lab_runs` persists deliveries, events, business actions, attempts, failures, access state, SLA, reconciliation result, snapshots, observed query count, processing time, and correlation evidence.

## Key code

- `app/Services/WebhookIngestor.php`
- `app/Jobs/ProvisionEntitlement.php`
- `app/Services/EntitlementProvisioner.php`
- `app/Services/RevenueIntegrityAuditor.php`
- `app/Services/ReconciliationService.php`
- `database/seeders/DemoScenarioSeeder.php`
- `database/migrations/2026_08_05_000200_create_paid_access_rescue_tables.php`
