# SaaS Revenue Integrity Rescue Lab

> Payment succeeded. Access failed. The system traced, recovered and stabilised the revenue path safely.

Rescue Lab is a working Laravel demonstration of a revenue-critical SaaS failure: a synthetic customer pays successfully, the provider delivers the same webhook repeatedly, and a queued entitlement job exhausts bounded retries. Access remains locked beyond its SLA until a transactionally safe reconciliation restores it. A second reconciliation is retained as a verified no-op.

## Evidence boundary

Every organisation, user, plan, subscription, invoice, payment, provider event, delivery, failure, timing, and revenue figure is synthetic lab evidence. The project has no external billing connection and does not claim real recovered customer revenue.

The implemented guarantee is:

> At-least-once event delivery with an idempotent, effectively-once business effect.

The project does not claim distributed exactly-once delivery.

## Stack

- Laravel 13 on PHP 8.5
- MySQL 8.4 as the system of record
- Valkey 8 as the Redis-compatible queue and Horizon store
- Laravel Horizon 5.48 and a dedicated Compose worker
- Blade, Vite 8, and framework-free browser JavaScript
- PHPUnit 12 and Playwright desktop/mobile coverage

## First run

Docker Desktop and `make` are the only host requirements.

```bash
make demo
```

The command refreshes dependencies in Docker when required, starts Laravel/MySQL/Valkey/Horizon, generates `APP_KEY` only when absent, applies migrations, creates the synthetic fixture only when absent, installs frontend dependencies and pinned Chromium, and builds assets.

Open [http://localhost:18080](http://localhost:18080):

```text
Email: operator@rescuelab.test
Password: rescue-demo
```

These credentials are synthetic, local-only defaults. Replace the seeded identity before any public deployment.

## Guided journey

Start with **Guided live incident** above the evidence tabs. Its eight controls persist one action at a time: successful payment, ten deliveries of one webhook, three queue failures, SLA detection, safe repair, and repeat no-op verification. The live facts and evidence tabs reload from MySQL after every action.

1. **Rescue Control Room:** connect the technical failure to Northstar Labs, USD 4,800 synthetic monthly exposure, and locked access.
2. **Customer Incident:** follow the actions just performed using stored correlation IDs.
3. **Root Cause:** inspect at-least-once delivery, the failed entitlement job, and the breached access SLA.
4. **Safe Recovery:** verify locked-to-active before/after state and the immutable repaired/no-op actions.
5. **Fragile vs Stabilised:** compare the same synthetic scenario with and without signature, inbox, idempotency, uniqueness, retry, and reconciliation controls.
6. **Engineering Evidence:** resolve product claims to tests, architecture, commands, and the runbook.

## Scenario commands

```bash
make simulate   # replay fragile and stabilised paths
make reset      # restore the active paid-but-locked incident
make reconcile  # repair once and verify the repeat no-op
make test       # run the complete local quality gate
make video      # create a narrated-caption prospect MP4 and poster in artifacts/
```

Equivalent Artisan commands are `lab:simulate`, `lab:reset`, and `lab:reconcile --verify-no-op`.

## Safety controls

- HMAC verification with an explicitly synthetic local signing credential
- Persistent delivery ledger plus unique provider event and delivery constraints
- Idempotent consumer and guarded subscription state transitions
- One entitlement per workspace/subscription/key
- Transaction and row locking around provisioning and reconciliation
- Three bounded job attempts with `[1, 5, 15]` second backoff
- Horizon and persisted provisioning-attempt visibility
- Immutable reconciliation actions with before/after snapshots
- Workspace-derived reads and mutations
- MySQL and Valkey are not bound to host ports
- `.env`, dependencies, compiled assets, logs, and test artifacts are ignored

## Verification

```bash
./vendor/bin/sail pint --test
./vendor/bin/sail test
./vendor/bin/sail npm run build
./vendor/bin/sail npm run test:e2e
./vendor/bin/sail composer audit --locked --no-interaction
./vendor/bin/sail npm audit --audit-level=low
```

The mandatory tests are in [`tests/Feature/PaidAccessRescueTest.php`](tests/Feature/PaidAccessRescueTest.php), [`tests/Unit/RevenueIntegrityAuditorTest.php`](tests/Unit/RevenueIntegrityAuditorTest.php), and [`tests/Browser/revenue-rescue.spec.js`](tests/Browser/revenue-rescue.spec.js). GitHub Actions repeats the same release gates in [`.github/workflows/ci.yml`](.github/workflows/ci.yml).

## Architecture and operations

- [Architecture](docs/ARCHITECTURE.md) documents event ingestion, queue processing, constraints, tenancy, detection, and reconciliation.
- [Operations](docs/OPERATIONS.md) is the setup, deployment, rollback, failed-job, reconciliation, and incident-response runbook.
- [Prospect demo](docs/PROSPECT_DEMO.md) provides an accurate five-minute walkthrough.
- [Current state](docs/CURRENT_STATE.md) states what is implemented and what remains outside the lab boundary.
- [Release audit](docs/RELEASE_AUDIT.md) records the observed public release-candidate checks and external deployment boundary.

## Limitations

- The payment provider and failure adapter are deterministic simulations.
- The lab accepts only its synthetic signed webhook contract.
- Reconciliation activates a synthetic entitlement; it does not call a real provider.
- No public signup, commercial billing, production identity provider, or customer-data import exists.
- A production deployment still requires approved infrastructure, secrets, TLS, monitoring, backups, and an operator access policy.
- No deployment, remote push, or external service configuration is performed by this repository.
