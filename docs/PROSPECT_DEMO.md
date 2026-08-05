# Prospect Demonstration

## Honest Demonstration Position

Rescue Lab is a working Laravel and MySQL application using a repeatable demonstration ledger. It demonstrates real detection rules, persistence, workflow, recovery accounting, reporting, and tenant isolation. It does not currently connect to a live billing provider or inspect a prospect's Laravel repository.

## Five-Minute Revenue Integrity Flow

Before the call:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail npm run build
```

Open `http://localhost:18080` and use the local demonstration credentials documented in `README.md`.

| Time | Action | Talk track |
| ---: | --- | --- |
| 0:00 | Sign in | "This is a tenant-isolated Revenue Operations workspace." |
| 0:20 | Reset the lab | "We are starting with a fresh monthly billing scenario." |
| 0:40 | Show $12,200 exposure | "The engine has quantified six revenue and billing failures." |
| 1:00 | Show exposure map | "Finance can prioritize by dollars rather than inspecting every invoice." |
| 1:20 | Open Northstar Labs | "A $4,800 payment failed because of insufficient funds." |
| 1:50 | Assign Maya Chen | "Every anomaly becomes owned work." |
| 2:10 | Move to Investigating | "The queue reflects the operational state." |
| 2:30 | Add a note | "Investigation context remains attached to the incident." |
| 2:50 | Mark Recovered | "Exposure falls to $7,400 and recovered value becomes $4,800." |
| 3:20 | Run another scan | "The same source anomaly is refreshed without duplicating the incident." |
| 3:50 | Show activity | "Scans and workflow changes remain auditable." |
| 4:10 | Export CSV | "Finance can use the register during close and reporting." |
| 4:30 | Show mobile | "Operators can manage the workflow away from a desktop." |

## Ten Prospect Use Cases

| Use case | Demonstration |
| --- | --- |
| Month-end exposure | Show total risk, incident count, and failure-mode breakdown |
| Failed-payment rescue | Investigate and recover Northstar Labs |
| Missing invoice | Explain Harbor & Pine's unbilled subscription |
| Contract underbilling | Show Orbit Freight's contract shortfall |
| Overdue collection | Show Summit AI's unpaid invoice |
| Duplicate prevention | Explain Pioneer Cloud's duplicate invoice |
| Post-cancel protection | Show Lumen Works' incorrect invoice |
| Team ownership | Assign incidents and add investigation notes |
| Recovery reporting | Show at-risk and recovered metrics change |
| Audit and close reporting | Run an idempotent scan and export CSV |

## Legacy Modernization Bridge

Use Revenue Integrity to connect business symptoms to technical modernization:

| Finding | Immediate rescue | Modernization cause |
| --- | --- | --- |
| Failed payment | Update method and retry | Webhook, retry, and dunning reliability |
| Missing invoice | Generate invoice | Scheduler, queue, and observability repair |
| Underbilling | Issue adjustment | Pricing and contract source of truth |
| Overdue invoice | Start collection | Automated reminders and escalation |
| Duplicate invoice | Void duplicate | Idempotency and event deduplication |
| Post-cancel invoice | Void or refund | Subscription lifecycle synchronization |

Core statement:

> We do not stop at flagging the problem. We rescue the immediate business outcome, trace the legacy workflow that allowed it, modernize the cause, and verify the result.

## Safe Claims

- "The audit engine reads normalized billing records from MySQL."
- "The current demonstration uses a representative billing ledger."
- "The workflow persists ownership, notes, status, and audit history."
- "Production connectors would feed the same normalized domain model."
- "The proposed Legacy Assessment module will add codebase evidence and modernization planning."

## Claims to Avoid

- "This is already connected to Stripe."
- "Recovered status proves that money moved."
- "The tool automatically modernizes any Laravel application."
- "This is a complete security or compliance audit."
- "The current application scans a prospect's repository."

## Planned Legacy Assessment Demonstration

The planned flow is: select a representative legacy project, run a real static assessment, inspect file-level evidence, map findings to business impact, create modernization waves, show one before-and-after fix, run verification tests, and export the assessment report.
