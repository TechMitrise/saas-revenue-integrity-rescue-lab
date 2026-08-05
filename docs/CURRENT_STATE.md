# Current State

Last reviewed: August 5, 2026

## Executive Summary

The repository contains a working local-first Revenue Integrity Rescue Lab. It is a real Laravel application backed by MySQL, not a static prototype. It uses a fixed demonstration billing ledger to detect six anomaly types and supports an authenticated incident workflow from detection through operational recovery.

The application is ready for local demonstrations. It is not yet deployed, connected to GitHub, connected to a live billing provider, or equipped with the proposed Legacy Laravel codebase assessment module.

## Implemented

- Laravel session authentication and logout
- Workspace-level tenant ownership
- MySQL schema for accounts, subscriptions, invoices, payments, incidents, notes, audit runs, and activities
- Deterministic demonstration dataset
- Revenue integrity audit engine
- Idempotent incident fingerprints
- Dashboard metrics and exposure breakdown
- Incident filtering and detail view
- Assignment and status changes
- Operator notes
- Recovery accounting
- Activity timeline
- CSV incident export
- Local demo reset
- Responsive desktop and mobile interface
- PHPUnit unit and feature tests against MySQL
- Playwright desktop and mobile end-to-end workflows
- Laravel Sail local environment

## Detection Rules

| Rule | Demonstration account | Exposure |
| --- | --- | ---: |
| Failed payment | Northstar Labs | $4,800 |
| Billing after cancellation | Lumen Works | $2,600 |
| Missing invoice | Harbor & Pine | $1,800 |
| Overdue invoice | Summit AI | $1,250 |
| Duplicate invoice | Pioneer Cloud | $950 |
| Underbilling | Orbit Freight | $800 |

Total seeded exposure is $12,200 across six incidents.

## Dynamic Actions

- Running a scan reads MySQL records and evaluates the audit rules.
- Repeated scans update existing incidents without duplicating them.
- Assignment, status, notes, and activity persist in MySQL.
- Marking an incident recovered updates at-risk and recovered metrics.
- Export generates a current CSV from persisted incidents.
- Reset recreates the local demonstration scenario.

## Fixed or Simulated Elements

- Source billing records are seeded rather than imported.
- No live Stripe, Chargebee, Recurly, ERP, or CRM connection exists.
- Recovery status does not retry payments, issue invoices, void duplicates, or move money.
- Recommended actions are deterministic rule output.
- The browser assignee list is currently fixed.
- No public self-service signup or password recovery is implemented.

## Routes

| Route | State |
| --- | --- |
| `/` | Redirects to `/app` |
| `/login` | Local operator login |
| `/app` | Revenue Integrity dashboard |
| `/api/dashboard` | Workspace dashboard data |
| `/api/audits` | Run integrity scan |
| `/api/incidents/{id}` | Update incident |
| `/api/incidents/{id}/notes` | Add note |
| `/api/incidents/export.csv` | Download report |
| `/api/demo/reset` | Local/testing reset only |

## Local Access

```text
URL: http://localhost:18080
Email: operator@rescuelab.test
Password: rescue-demo
```

## Verification Baseline

- Laravel Pint: passing
- PHPUnit: 10 tests, 50 assertions, passing on MySQL
- Vite production build: passing
- Playwright: desktop and mobile workflows passing
- Composer audit: no known advisories at last check
- npm audit: no known vulnerabilities at last check

## Not Yet Implemented

- Public TMPL marketing page
- Legacy Laravel codebase assessment
- Modernization-wave planner
- Before-and-after code verification view
- Executive PDF assessment report
- GitHub Actions
- Git remote or initial commit
- Production build configuration
- Public deployment, domain, monitoring, or backups
- Live billing-data ingestion
