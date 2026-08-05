# Operations and Incident Runbook

## First run

```bash
make demo
```

`make demo` is idempotent: it creates `.env` when missing, refreshes Composer dependencies when their declarations change, starts the stack, generates `APP_KEY` only when blank, applies pending migrations, creates missing lab data, installs Node dependencies and Chromium, and builds assets. It does not rotate an existing key.

## Services

```bash
./vendor/bin/sail ps
./vendor/bin/sail logs laravel.test
./vendor/bin/sail logs queue
./vendor/bin/sail artisan horizon:status
```

Laravel is available at `http://localhost:18080`. MySQL and Valkey have no host port bindings. Horizon uses the internal `redis` connection and is authorised only according to `HorizonServiceProvider` outside local environments.

## Scenario operations

```bash
make simulate
make reset
make reconcile
```

- `lab:simulate` recreates and reports both controlled paths.
- `lab:reset` restores the active paid-but-locked incident.
- `lab:reconcile --verify-no-op` repairs access and immediately verifies the repeat no-op.

These commands delete and recreate only synthetic data in the selected lab workspace. Never enable them for customer or production data.

## Quality gate

```bash
make test
./vendor/bin/sail composer audit --locked --no-interaction
./vendor/bin/sail npm audit --audit-level=low
./vendor/bin/sail composer validate --strict --no-check-publish
./vendor/bin/sail composer check-platform-reqs --lock
```

PHPUnit uses Sail's `testing` MySQL database and `QUEUE_CONNECTION=sync`. Playwright runs the complete journey in desktop Chromium and Pixel 7 emulation against the running application.

## Failed-job recovery

1. Inspect `provisioning_attempts` and the Customer Incident timeline before retrying anything.
2. Check Horizon with `./vendor/bin/sail artisan horizon:status` and the authenticated Horizon dashboard where authorised.
3. List broker failures with `./vendor/bin/sail artisan queue:failed`.
4. Confirm the failed job belongs to the intended workspace and entitlement correlation ID.
5. Prefer `lab:reconcile` for the demonstrated paid-but-locked state; it is lock-safe and auditable.
6. Retry a real failed job only after removing its root cause: `./vendor/bin/sail artisan queue:retry <uuid>`.
7. Verify access, entitlement count, reconciliation history, and SLA state after recovery.

Do not blindly run `queue:retry all` in a multi-tenant production environment.

## Reconciliation incident response

1. Confirm payment status is `succeeded` and subscription state is `active`.
2. Confirm the entitlement remains `locked` and the SLA is breached.
3. Capture provider event ID, delivery IDs, job UUIDs, and entitlement ID from the incident.
4. Run reconciliation once.
5. Verify the action reports `repaired`, one business effect, and entitlement count one.
6. Run reconciliation again.
7. Verify `no_op`, zero business effects, and entitlement count still one.
8. Preserve both immutable action IDs in the incident record.

## Deployment guidance

No production host is selected or configured. An approved deployment must provide:

- PHP 8.5-compatible Laravel runtime
- managed MySQL 8.4-compatible database
- managed Redis/Valkey with persistence suitable for Horizon
- separate web and Horizon worker processes
- TLS termination, private database/cache networking, and secret injection
- `APP_ENV=production`, `APP_DEBUG=false`, a unique `APP_KEY`, and a non-demo identity
- scheduler entry for `php artisan schedule:run` and Horizon snapshots
- health checks for `/up`, database, cache, and worker heartbeat
- central logs, error monitoring, queue alerts, backups, and tested restore

Deploy with immutable application artifacts, then run `php artisan migrate --force`, `php artisan horizon:terminate`, and a smoke test. Never run `migrate:fresh`, seeders, reset, or simulation commands against production data.

## Rollback

1. Stop new traffic or put the application into maintenance mode when required.
2. Stop or terminate Horizon so workers do not execute code across releases.
3. Roll back the application artifact to the previous known-good version.
4. Roll back database migrations only when the migration is explicitly reversible and no new production data would be lost.
5. Prefer a forward fix for additive schema changes.
6. Restart Horizon, exit maintenance, and verify payment/webhook/entitlement health.
7. Preserve webhook inbox and reconciliation history; never delete them to make a rollback appear clean.

## Backup and restore

Back up MySQL and Valkey according to the hosting provider's supported policy. A restore test must verify provider-event uniqueness, entitlement uniqueness, unresolved incidents, reconciliation actions, and queue state. Application files alone are not a recoverable system.

## Secrets and access

`.env` is ignored. The committed webhook credential is explicitly synthetic and local-only. Production must inject a rotated credential through its secret manager. Replace demo credentials, restrict Horizon through the gate, keep MySQL/Valkey private, and do not log signatures, passwords, or raw secrets.
