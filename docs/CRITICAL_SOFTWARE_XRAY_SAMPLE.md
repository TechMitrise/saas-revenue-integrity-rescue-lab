# Critical Software X-Ray Sample

This compact X-Ray sample is derived from the synthetic Rescue Lab evidence. It shows the shape of a paid diagnostic deliverable for one revenue-critical path. It is not a decorative report, real client finding or production outcome claim.

## Critical-Path Map

```text
Synthetic payment accepted
  -> signed provider webhook delivered ten times
  -> persistent inbox stores one provider event
  -> idempotent consumer activates subscription once
  -> queued entitlement provisioning runs three bounded attempts
  -> entitlement remains locked
  -> SLA audit opens paid-but-locked incident
  -> lock-protected reconciliation activates existing entitlement
  -> repeat reconciliation returns no_op
```

## Expected Versus Actual State

| Checkpoint | Expected | Actual in fragile run |
| --- | --- | --- |
| Payment | `succeeded` | `succeeded` |
| Provider delivery | At-least-once delivery retained safely | Ten deliveries retained |
| Provider event | One logical event | One event |
| Business effect | One subscription transition | One business effect |
| Entitlement | Active within SLA | Locked after three failed attempts |
| Incident | No paid-but-locked account | One open paid-but-locked incident |
| Recovery repeat | Safe no-op | `no_op`, zero effects, entitlement count one |

## Evidence Timeline

| Order | Evidence | What it proves |
| ---: | --- | --- |
| 1 | USD 4,800 synthetic payment succeeds | Cash path is healthy |
| 2 | Ten delivery IDs map to one provider event | Provider retry/duplicate delivery is expected input |
| 3 | Subscription transition records one business effect | Idempotency prevents duplicate subscription effects |
| 4 | Three provisioning attempts fail with controlled timeout evidence | Fulfilment work exhausts bounded retries |
| 5 | Entitlement remains `locked` beyond SLA | Customer value is not delivered |
| 6 | Audit opens one paid-but-locked incident | The business-impacting divergence is detectable |
| 7 | First reconciliation records `repaired` and one effect | Recovery changes the existing entitlement once |
| 8 | Second reconciliation records `no_op` and zero effects | Recovery is repeat-safe |

## Root-Cause Hypothesis Supported By Evidence

The supported hypothesis is: at-least-once webhook delivery reached a fragile fulfilment path where entitlement provisioning exhausted bounded retries, leaving a paid active subscription with locked access until reconciliation ran.

The Lab does not support claims of real customer impact, payment-provider failure, distributed exactly-once delivery or production revenue recovery.

## Risk Ranking

| Rank | Risk | Severity | Evidence |
| ---: | --- | --- | --- |
| 1 | Paid customer remains locked after successful payment | Critical | Payment `succeeded`, entitlement `locked`, SLA breached |
| 2 | Failed async work lacks automatic closure | High | Three persisted provisioning failures before recovery |
| 3 | Manual recovery could duplicate effects if not guarded | High | Repeat recovery is explicitly tested as `no_op` |
| 4 | Duplicate delivery can amplify unsafe consumers | Medium | Ten deliveries intentionally replay one provider event |
| 5 | Weak observability delays commercial triage | Medium | Correlation trail is required to join payment, webhook, queue and entitlement evidence |

## Immediate Containment

- Freeze broad retries until provider event, entitlement and subscription identifiers are confirmed.
- Preserve webhook delivery, job-attempt and entitlement evidence.
- Identify all active paid subscriptions with locked entitlements beyond SLA.
- Use a lock-protected repair path that updates existing entitlements instead of inserting replacements.
- Run the repair twice in a controlled environment to prove the second pass is harmless.

## Recovery And Rollback Direction

Recovery should be transactionally scoped by workspace, subscription, incident and entitlement. The first successful repair should retain before/after state and business-effect count. Rollback should preserve webhook inbox and reconciliation actions; for additive schema or evidence tables, prefer forward fixes over deleting immutable evidence.

## Prioritised 30-Day Rescue Roadmap

| Window | Priority | Acceptance evidence |
| --- | --- | --- |
| Days 1-3 | Map payment-to-value identifiers and define the access SLA | One path map and one query that finds paid-but-locked state |
| Days 4-7 | Persist provider deliveries and logical events with uniqueness constraints | Duplicate delivery replay creates one logical event |
| Days 8-12 | Guard subscription and entitlement transitions | Replayed events do not create duplicate entitlements |
| Days 13-17 | Bound queue retries and retain failure evidence | Exhausted attempts remain visible with job/correlation IDs |
| Days 18-23 | Build lock-protected reconciliation with before/after evidence | First repair changes one entitlement; repeat is `no_op` |
| Days 24-30 | Add runbook, browser proof and release tests | Reset, replay, recovery, no-op and screenshots pass from a fresh local run |

