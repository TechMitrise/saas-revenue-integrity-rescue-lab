# SaaS Revenue Integrity Rescue Lab

A tenant-aware Laravel application for detecting, prioritizing, and resolving SaaS billing and collection anomalies. The local rescue scenario turns subscription, invoice, and payment records into an operational incident queue with revenue exposure, evidence, recommended actions, ownership, notes, recovery tracking, and an audit trail.

## Stack

- Laravel 13.8 on PHP 8.5
- MySQL 8.4
- Laravel Sail and Docker
- Blade, Vite, and framework-free browser JavaScript
- PHPUnit for unit and feature coverage
- Playwright with headless Chromium for end-to-end coverage

## Start locally

Docker Desktop is the only host dependency. PHP, Composer, Node, and MySQL run through Sail.

```bash
cp .env.example .env
docker run --rm -v "$PWD:/app" -w /app composer:2 composer install
./vendor/bin/sail up -d --build
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

Open [http://localhost:18080](http://localhost:18080) and use the local demo account:

```text
Email: operator@rescuelab.test
Password: rescue-demo
```

The application uses host port `18080` for Laravel and `13307` for optional direct MySQL access. Containers communicate internally on the standard ports.

## Rescue workflow

1. Sign in to the workspace.
2. Review monitored MRR, active exposure, recovered value, and billing health.
3. Select an incident to inspect its evidence and recommended action.
4. Assign an owner, move the incident through investigation, and record notes.
5. Mark verified recoveries as recovered and watch risk metrics update.
6. Run another integrity scan to refresh findings without creating duplicates.
7. Export the incident register as CSV when needed.

The seeded scenario detects six failure modes: payment failure, missing invoice, underbilling, overdue invoice, duplicate invoice, and billing after cancellation.

## Verification

```bash
./vendor/bin/sail pint --test
./vendor/bin/sail test
./vendor/bin/sail npm run build
./vendor/bin/sail npx playwright install chromium
./vendor/bin/sail npm run test:e2e
```

The end-to-end workflow runs in desktop and mobile Chromium. It signs in, resets the scenario, opens the failed-payment incident, assigns it, adds an investigation note, marks it recovered, confirms metric changes, reruns the audit, and signs out.

## Project guide

- [Architecture](docs/ARCHITECTURE.md) describes tenancy, data flow, schema, and audit behavior.
- [Operations](docs/OPERATIONS.md) covers startup, testing, reset, troubleshooting, and production guardrails.
- [Current state](docs/CURRENT_STATE.md) records exactly what is implemented and what remains simulated.
- [Product scope](docs/PRODUCT_SCOPE.md) separates the shipped Revenue Integrity module from proposed Legacy Assessment work.
- [Decisions](docs/DECISIONS.md) preserves the product and technical choices already agreed.
- [Roadmap](docs/ROADMAP.md) defines the phased path from baseline protection through market launch.
- [Prospect demo](docs/PROSPECT_DEMO.md) provides the honest talk track and demonstration sequence.
- [Growth engine](docs/GROWTH_ENGINE.md) captures positioning, offers, acquisition, and sales operations.

No remote repository or external SaaS integration is configured. All current application data remains in the local MySQL Docker volume.
