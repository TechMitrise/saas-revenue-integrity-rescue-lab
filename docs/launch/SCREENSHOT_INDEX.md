# Proof Screenshot Index

All screenshots use a 1440 x 900 browser viewport and the same synthetic Northstar Labs fixture. Generate them with `make screenshots`.

| # | File | Caption | Evidence boundary |
| ---: | --- | --- | --- |
| 1 | [`01-rescue-control-room.png`](../../public/images/proof/01-rescue-control-room.png) | Payment succeeded; access remained locked beyond the entitlement SLA. | Synthetic account and USD 4,800 monthly exposure |
| 2 | [`02-customer-incident-timeline.png`](../../public/images/proof/02-customer-incident-timeline.png) | One correlation trail connects payment, webhook delivery, queue attempts and SLA breach. | Synthetic identifiers and deterministic timings |
| 3 | [`03-root-cause-evidence.png`](../../public/images/proof/03-root-cause-evidence.png) | Ten retained deliveries, one provider event, three failed attempts and one breached SLA. | Controlled webhook and failure adapter |
| 4 | [`04-safe-recovery-before-after.png`](../../public/images/proof/04-safe-recovery-before-after.png) | Lock-protected reconciliation changes the existing entitlement from locked to active once. | Synthetic entitlement and immutable action |
| 5 | [`05-repeat-no-op-proof.png`](../../public/images/proof/05-repeat-no-op-proof.png) | Repeat reconciliation records `no_op`, zero additional effects and entitlement count one. | Application-level repeat-safety evidence |
| 6 | [`06-fragile-vs-stabilised.png`](../../public/images/proof/06-fragile-vs-stabilised.png) | The same fixture behaves differently after signature, inbox, uniqueness, retry and reconciliation controls. | Lab comparison, not a production before/after claim |

Do not crop out the synthetic-evidence label, add customer logos or present these frames as a real client case study.
