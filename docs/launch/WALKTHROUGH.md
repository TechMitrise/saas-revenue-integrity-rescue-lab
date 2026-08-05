# 105-Second Rescue Lab Walkthrough

## Recording title

**Payment Succeeded. Access Failed. A Laravel Revenue Path Rescue in 105 Seconds**

## Suggested description

A controlled synthetic Laravel/MySQL incident showing how duplicate signed webhooks, queue failures and slow entitlement processing can leave a paying SaaS customer locked out. The lab correlates the evidence, quantifies synthetic exposure, repairs access once, verifies a repeat no-op and compares fragile with stabilised controls.

All organisations, payments, failures, timings and revenue figures are synthetic. The demonstrated guarantee is at-least-once event delivery with an idempotent, effectively-once business effect.

## Timed shot list, actions and captions

| Time | Browser action | On-screen caption | Human voice script |
| ---: | --- | --- | --- |
| 0:00-0:10 | Open the proof hero, then enter the live lab | `Payment succeeded. Access failed.` | "This is the failure that matters: a customer has paid, but the value they bought never became available. In a recurring-revenue product, that is both a reliability incident and a churn risk." |
| 0:10-0:23 | Start the guided incident and deliver the webhook ten times | `USD 4,800 paid / 10 deliveries / 1 event / 1 effect` | "Northstar Labs is synthetic. Its USD 4,800 payment succeeds. The same signed webhook arrives ten times, but the persistent inbox and idempotent consumer produce one logical event and one business effect." |
| 0:23-0:36 | Run queue attempts 1, 2 and 3 | `3 bounded attempts / 3 persisted failures` | "Provisioning is the weak link. The real entitlement job retries three times, records each controlled timeout and still leaves the existing entitlement locked." |
| 0:36-0:50 | Run the SLA audit, then open Customer Incident | `One correlation trail: payment -> webhook -> queue -> entitlement` | "The audit correlates payment, provider delivery, queue attempt and entitlement state. It detects one paid-but-locked account beyond the configured SLA and reports USD 4,800 of synthetic monthly exposure." |
| 0:50-1:02 | Open Root Cause | `Cash was healthy. Fulfilment was not.` | "The payment path is healthy. At-least-once delivery met fragile fulfilment, bounded retries exhausted, and no safe automatic recovery closed the gap." |
| 1:02-1:17 | Open Safe Recovery and run reconciliation once | `locked -> active / 1 business effect` | "Recovery scopes the existing entitlement inside a transaction with row locks and uniqueness constraints. Access moves from locked to active, and the immutable action retains before-and-after evidence." |
| 1:17-1:28 | Run reconciliation again | `repeat result: no_op / 0 effects` | "Now I run the same recovery again. It returns no-op, creates zero additional effects and keeps entitlement count at one. That is the safety proof." |
| 1:28-1:40 | Open Fragile vs Stabilised | `Same fixture. Different controls.` | "The stabilised path verifies signatures, persists the inbox, enforces uniqueness, bounds retries and activates one entitlement within SLA. This is effectively-once business behaviour, not a distributed exactly-once claim." |
| 1:40-1:45 | Return to proof CTA | `Request a Legacy SaaS Risk Triage` | "If a paid workflow in your SaaS depends on webhooks, queues or legacy state transitions, let us map where customer value can break after payment." |

## Browser sequence

1. Run `make reset` and open `/proof`.
2. Select **Run the 8-action rescue** and sign in.
3. Start payment, duplicate delivery, three queue attempts and the SLA audit.
4. Open Customer Incident and Root Cause.
5. Run reconciliation once and then again.
6. Open Fragile vs Stabilised.
7. End on the Risk Triage CTA, not the login screen.

## Recording checklist

- Use a 1440 x 900 browser viewport at 100% zoom.
- Close bookmarks, notifications, password prompts and unrelated tabs.
- Run `make reset` immediately before recording.
- Confirm the synthetic-evidence label is visible.
- Speak at 135-145 words per minute and pause on each state change.
- Do not call the webhook or reconciliation literally exactly-once.
- Do not imply Northstar Labs is a real client.
- Keep the cursor still while explaining evidence.
- Record human voice in a quiet room; do not use an artificial testimonial or voice.
- End between 90 and 120 seconds.

The automated caption-only visual can still be created with `make video`; use this script for Mohit's final human narration.
