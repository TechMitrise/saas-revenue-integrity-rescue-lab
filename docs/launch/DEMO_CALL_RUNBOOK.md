# Fifteen-Minute Demo-Call Playbook

## Call outcome

Decide whether one paid-customer path has enough business relevance and accessible evidence for a Legacy SaaS Risk & Revenue Audit. Do not attempt to diagnose the prospect's system for free.

## 0:00-2:00 | Frame and qualify

Say:

> I will show one controlled failure: payment succeeds, access does not, and the system has to trace and repair the gap safely. Before I do, which customer value in your product depends on a webhook, queue or background workflow after payment?

Ask no more than two:

1. What happens after a successful payment before the customer receives value?
2. Which failure is most expensive today: delay, duplicate effect, lockout or manual recovery?

Transition:

> I am not assuming your cause is the same. Watch the evidence method, then we will map whether it applies.

## 2:00-9:30 | Exact demonstration order

| Time | Screen/action | Transition |
| ---: | --- | --- |
| 2:00 | Control Room: payment succeeded, access locked, synthetic USD 4,800 exposure | "Start with customer and revenue impact, not logs." |
| 3:00 | Guided webhook action: ten deliveries, one event, one effect | "Delivery can repeat; the business transition must not." |
| 4:00 | Three queue attempts | "The failure remains bounded and inspectable instead of disappearing into retry noise." |
| 5:00 | Customer Incident timeline | "One correlation trail connects cash, event delivery, work and access state." |
| 6:00 | Root Cause | "Cash was healthy; fulfilment exhausted its safe path and breached the SLA." |
| 7:00 | Safe Recovery once | "The repair acts on the existing entitlement under locks and retains before/after evidence." |
| 8:00 | Safe Recovery again | "The repeat is a verified no-op with zero additional effects." |
| 8:45 | Fragile vs Stabilised | "Same fixture, different controls; this is effectively-once business behaviour, not distributed exactly-once delivery." |

## 9:30-12:00 | Map relevance

Ask:

- Which identifier would join payment, account and delivered value in your system?
- Where can work fail after the provider has accepted payment?
- What evidence persists after retries are exhausted?
- Who can repair the state today, and how is a repeat made safe?
- Is there an agreed payment-to-value SLA?

Do not ask for credentials, raw payloads or customer records on this call.

## 12:00-14:00 | Offer the Audit

Say:

> The next useful step is not a rewrite estimate. It is a fixed-scope Legacy SaaS Risk & Revenue Audit of one selected path. Over five business days, I map the state transitions, reconstruct one failure, rank the control gaps and give you a rescue sequence with acceptance evidence. The typical fixed-fee range is USD 1,500-3,000 after scope is confirmed.

Boundary:

> Implementation is separate. You can use the evidence with your team even if we do not run the Stabilisation Sprint.

## 14:00-15:00 | Close

Ask:

> Is there one path important enough to put through that audit now?

If yes: send the intake and schedule a scope call with engineering and revenue owners.  
If unclear: send the 107-second walkthrough and ask for one sanitized incident summary.
If no fit: close without forcing a broad consulting pitch.

## What not to diagnose for free

- exact root cause without code or evidence;
- migration or rewrite scope;
- production repair commands;
- security or compliance posture;
- team performance or blame;
- implementation estimates beyond the Audit.

## Follow-up

Use the **Post-demo fit** message in [`FOLLOW_UP_TEMPLATES.md`](FOLLOW_UP_TEMPLATES.md) on the same day.
