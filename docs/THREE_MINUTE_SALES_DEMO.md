# Three-Minute Sales Demonstration Path

Use this as the short internal path when a buyer has only a few minutes. It uses the working Rescue Lab screens and synthetic evidence only.

## Exact Screens And Actions

| Time | Screen/action | Plain-language narration |
| ---: | --- | --- |
| 0:00-0:25 | `/proof` hero and first proof frame | "This is the business failure: payment succeeded, but the customer stayed locked out. Northstar Labs and the USD 4,800 figure are synthetic, but the engineering path is executable." |
| 0:25-0:55 | Open the lab, show Control Room | "The operator starts with impact: one affected account, active paid subscription, locked access and a visible correlation trail." |
| 0:55-1:25 | Customer Incident | "The system connects payment, webhook deliveries, queue work, entitlement state and SLA evidence instead of leaving the team to stitch logs together manually." |
| 1:25-1:50 | Root Cause | "Cash collection worked. Fulfilment did not. Ten deliveries became one event and one business effect, but three bounded provisioning attempts failed and access remained locked." |
| 1:50-2:25 | Safe Recovery, run reconciliation once if the fixture is locked | "Recovery acts on the existing entitlement under transaction and locks. Access moves from locked to active and the before/after evidence is retained." |
| 2:25-2:45 | Safe Recovery, verify repeat no-op | "The second run is the safety proof: no-op, zero additional effects and entitlement count remains one." |
| 2:45-3:00 | Engineering Evidence or README proof package | "A paid Critical Software X-Ray applies this same evidence standard to one selected path in your system: map the critical path, reconstruct the failure, rank controls and return a rescue roadmap." |

## Five Qualification Questions

1. After payment succeeds in your product, what still has to happen before the customer receives value?
2. Which identifier joins payment, account, subscription and delivered access?
3. Where can that path fail asynchronously: webhook, queue, entitlement, credits, seats or manual approval?
4. How do you prove a repair is safe to run twice?
5. Is there a defined payment-to-value SLA, and who sees it when it is breached?

## Transition To A Paid Critical Software X-Ray

Say:

> I am not assuming your system has this exact failure. The X-Ray is the fixed-scope next step: choose one revenue-critical path, map expected versus actual state, inspect the evidence, rank the risks and leave you with containment, recovery and a 30-day rescue sequence your team can verify.

Keep the boundary clear: no production credentials are requested on the first call, implementation is separate, and all Rescue Lab evidence shown in this demo is synthetic.

