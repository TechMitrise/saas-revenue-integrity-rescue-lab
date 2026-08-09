# Prospect Demonstration

Run `make reset`, sign in, and start at **Guided live incident**. Each click performs one real Laravel application action and reloads the observed state from MySQL.

| Time | Screen | Evidence to show |
| ---: | --- | --- |
| 0:00 | Guided steps 1-2 | Record the USD 4,800 payment, then deliver the signed webhook ten times. Show ten deliveries, one event, and one business effect. |
| 0:50 | Guided steps 3-5 | Run the same entitlement job three times. Show each controlled timeout persisted while entitlement count remains one. |
| 1:40 | Guided step 6 | Run the SLA audit. Show one paid-but-locked incident and USD 4,800 of synthetic monthly exposure. |
| 2:20 | Guided steps 7-8 | Repair once, then repeat. Show active access, one first effect, `no_op`, zero repeat effects, and one entitlement. |
| 3:10 | Incident and Root Cause | Show the single correlation trail, retained delivery IDs, failed attempts, and SLA evidence produced by those clicks. |
| 4:00 | Fragile vs Stabilised | Compare the same scenario with one event/action in both modes but only the stabilised path activates within SLA. |
| 4:35 | Engineering Evidence | Resolve claims to tests, architecture, commands, CI, and runbook. |

For a ready-to-record 107-second human script, captions and shot list, use [`launch/WALKTHROUGH.md`](launch/WALKTHROUGH.md). Run `make video` to create the matching captioned browser visual at `artifacts/rescue-lab-prospect-demo.mp4`; the command also creates a poster and restores the active incident fixture.

Use this exact guarantee: "At-least-once event delivery with an idempotent, effectively-once business effect."

Always state that all data, timings, failures, and revenue figures are synthetic lab evidence.
