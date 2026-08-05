# Architecture

## System shape

The application is a server-rendered Laravel monolith with a small JSON interaction layer. Blade renders authentication and the dashboard shell, Vite compiles the CSS and browser JavaScript, Laravel handles every read and mutation, and MySQL is the system of record.

```text
Browser
  -> Laravel web routes and session authentication
  -> workspace-scoped controllers
  -> audit and dashboard services
  -> Eloquent models
  -> MySQL 8.4
```

This shape keeps the local setup compact while retaining clear boundaries between HTTP, domain rules, persistence, and presentation.

## Tenant boundary

Each user belongs to one workspace. Accounts, subscriptions, invoices, payments, incidents, audit runs, and activities carry a `workspace_id`. Controllers derive that ID from the authenticated user instead of accepting it from the request. Incident updates and notes query through the current workspace and return `404` for records owned by another tenant.

The current model is suitable for one-workspace-per-user access. A future many-to-many membership model can replace `users.workspace_id` without changing the domain tables.

## Data model

| Table | Purpose |
| --- | --- |
| `workspaces` | Tenant identity, timezone, and currency |
| `users` | Session-authenticated revenue operators |
| `accounts` | Customer accounts and owners |
| `subscriptions` | Expected monthly contract value and lifecycle |
| `invoices` | Billing period, amount, status, and due date |
| `payments` | Collection attempts and failure evidence |
| `incidents` | Idempotent audit findings and recovery state |
| `incident_notes` | Operator investigation context |
| `audit_runs` | Scan counts, timing, and initiator |
| `activities` | User-visible operational audit trail |

Foreign keys enforce ownership and cleanup. The unique `(workspace_id, fingerprint)` index prevents repeated scans from duplicating the same incident.

## Audit engine

`RevenueIntegrityAuditor` loads the current billing period for one workspace, evaluates deterministic rules, and writes findings in a transaction. Every finding contains a stable fingerprint, severity, revenue impact, human-readable evidence, and a recommended action.

| Rule | Trigger | Exposure |
| --- | --- | --- |
| Payment failure | Failed collection with no successful payment | Invoice amount |
| Missing invoice | Active subscription with no current-period invoice | Expected MRR |
| Underbilling | Invoice below expected monthly amount | Contract shortfall |
| Overdue invoice | Past due with no successful or explicitly failed payment | Invoice amount |
| Duplicate invoice | Multiple active invoices for one subscription and period | Smallest duplicate amount |
| Post-cancel billing | Non-void invoice after subscription cancellation | Invoice amount |

Repeated scans update `last_detected_at` and evidence on existing incidents. Operator status and notes are preserved, so the audit refresh is idempotent and does not erase investigation work.

## HTTP surface

All application endpoints use Laravel session authentication and CSRF protection.

| Method | Route | Purpose |
| --- | --- | --- |
| `GET` | `/login` | Login form |
| `POST` | `/login` | Start authenticated session |
| `POST` | `/logout` | End authenticated session |
| `GET` | `/app` | Dashboard shell |
| `GET` | `/api/dashboard` | Tenant metrics, incidents, breakdown, and activity |
| `POST` | `/api/audits` | Run an integrity scan |
| `PATCH` | `/api/incidents/{id}` | Update status or assignee |
| `POST` | `/api/incidents/{id}/notes` | Add an operator note |
| `GET` | `/api/incidents/export.csv` | Download the incident register |
| `POST` | `/api/demo/reset` | Restore local demo data; unavailable outside local/testing |

## Key locations

- `app/Services/RevenueIntegrityAuditor.php`: detection and idempotent persistence
- `app/Services/DashboardService.php`: metrics and browser response projection
- `app/Http/Controllers/Api`: authenticated workflow endpoints
- `database/migrations`: MySQL schema and constraints
- `database/seeders`: deterministic rescue scenario
- `resources/views`: login and dashboard Blade templates
- `resources/js/app.js`: browser workflow
- `resources/css/app.css`: responsive visual system
- `tests/Unit`, `tests/Feature`, `tests/Browser`: layered verification
