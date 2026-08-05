# Legacy SaaS Risk & Revenue Audit

Service focus: revenue-path integrity

## The offer

A fixed-scope technical and operational assessment for Laravel SaaS teams that collect payment before provisioning access, credits, seats, subscriptions or account upgrades.

The audit answers one question:

> Where can paid revenue become delayed, duplicated, inaccessible or operationally unsafe before the customer receives value?

## Best fit

- Laravel SaaS with payment-provider webhooks and queued fulfilment.
- Teams seeing paid-but-locked tickets, delayed upgrades, duplicate side effects or manual repair work.
- A legacy revenue path that cannot justify a rewrite but needs safer controls.
- Engineering leaders who need evidence and sequencing before approving remediation.

The audit is not a fit for pre-revenue products, general feature delivery or a full security/compliance assessment.

## Five-business-day shape

| Day | Work | Evidence produced |
| ---: | --- | --- |
| 0 | Intake and access boundary | Approved path, redaction plan and named owners |
| 1 | Map payment to customer value | System, state-transition and ownership map |
| 2 | Trace one failure path | Correlation trail and reproducible or evidence-backed failure narrative |
| 3 | Evaluate controls | Risk register covering idempotency, ordering, retries, uniqueness, observability and recovery |
| 4 | Prioritise rescue work | Sequenced recommendations with risk, effort and verification criteria |
| 5 | Executive and engineering readout | Decision brief, evidence pack and agreed next action |

## Deliverables

1. One revenue-path map from payment acceptance to customer value.
2. Ranked integrity risk register with evidence, impact, likelihood and owner.
3. One deep incident or failure-path reconstruction using approved data.
4. Control assessment for webhook ingestion, queue processing, state transitions and reconciliation.
5. Prioritised 30/60/90-day rescue plan with acceptance evidence for each action.
6. Executive summary and 60-minute technical readout.

## Evidence standard

Every finding is labelled as one of:

- **Observed:** directly supported by code, data, logs or a controlled reproduction.
- **Correlated:** supported by multiple signals but not yet reproduced.
- **Hypothesis:** plausible and explicitly awaiting evidence.

No hypothesis is presented as a confirmed incident.

## Client inputs

- one engineering owner and one business/revenue owner;
- architecture or repository context for the approved path;
- sanitized event, queue and incident evidence when available;
- read-only access where feasible, time-boxed and revocable;
- two 60-minute sessions: kickoff and readout.

Use [`AUDIT_INTAKE.md`](AUDIT_INTAKE.md) before requesting access.

## Explicit exclusions

- implementation, migration or production changes;
- broad codebase review outside the selected revenue path;
- penetration testing, compliance certification or legal advice;
- financial audit or validation of recognised revenue;
- uptime, revenue-recovery or zero-incident guarantees;
- retention of unredacted customer or payment data.

## Commercial model

The recommended fixed-fee range is **USD 1,500-3,000**, confirmed after a fit call establishes path complexity, evidence availability and access constraints. Tax, payment schedule, legal entity and jurisdiction remain client-specific fields in [`PROPOSAL_TEMPLATE.md`](PROPOSAL_TEMPLATE.md).

Implementation is a separate decision. The audit must remain useful even if the client selects another team to execute the rescue plan.

When implementation is approved, the next engagement is a separately scoped **Stabilisation Sprint** focused on the highest-priority controls and their acceptance evidence. Modernisation or a reliability retainer is proposed only after the critical path is stable.

## Success criteria

The audit is complete when the client can answer:

1. Which paid-customer path is most exposed?
2. What evidence confirms or weakens each risk?
3. Which control should change first, and why?
4. How will the team prove the repair is safe?
5. What should be monitored after release?

## Call to action

Book a short fit call with the revenue-path owner. Bring one recent paid-access, duplicate-event, delayed-upgrade or manual-recovery example. No production credentials are required for the first call.
