# Five-Minute Demonstration

Run `make reset`, sign in, and keep the browser at the Control Room.

| Time | Screen | Evidence to show |
| ---: | --- | --- |
| 0:00 | Control Room | Synthetic Northstar Labs paid USD 4,800 but remains locked beyond the SLA. |
| 0:45 | Customer Incident | One payment, one provider event, ten retained deliveries, three failed job attempts, one correlation trail. |
| 1:35 | Root Cause | At-least-once delivery met a fragile provisioning path and bounded retries exhausted. |
| 2:15 | Safe Recovery | Run reconciliation; show locked-to-active state, one entitlement, and one business effect. |
| 3:00 | Safe Recovery | Run it again; show `no_op`, zero effects, and entitlement count still one. |
| 3:35 | Fragile vs Stabilised | Compare the same scenario with one event/action in both modes but only the stabilised path activates within SLA. |
| 4:25 | Engineering Evidence | Resolve claims to tests, architecture, commands, CI, and runbook. |

Use this exact guarantee: "At-least-once event delivery with an idempotent, effectively-once business effect."

Always state that all data, timings, failures, and revenue figures are synthetic lab evidence.
