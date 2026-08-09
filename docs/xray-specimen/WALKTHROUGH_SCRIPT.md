# 90-120 Second Walkthrough Script

Title: **Critical Software X-Ray: Payment Succeeded, Access Failed**

Use this with the protected specimen route after unlocking it.

| Time | Screen | Script |
| ---: | --- | --- |
| 0:00-0:10 | Hero and boundary | "This is a synthetic professional-service specimen, not a client case study. It shows what a buyer receives from a Critical Software X-Ray: evidence before rewrite, and evidence before automation." |
| 0:10-0:25 | Executive Risk Brief | "The business failure is simple: payment succeeded, but customer access remained locked. The confirmed consequence is one synthetic paid subscription beyond its entitlement SLA. The wider exposure is unknown until a real X-Ray inspects the buyer's workflow." |
| 0:25-0:42 | Critical-path map | "The path is payment, webhook, processing, queue, subscription, entitlement and access. Payment and idempotent webhook processing held. The first proven divergence is the queue job: three bounded provisioning attempts failed before entitlement activation." |
| 0:42-0:58 | State matrix and timeline | "The matrix separates expected, incident and recovered state. The timeline ties each claim to evidence IDs, timestamps and correlation identifiers instead of asking the buyer to trust a narrative." |
| 0:58-1:14 | Root cause, rules and findings | "The proximate failure is provisioning timeout. The deeper issue is hidden business rules around paid status, entitlement consistency, replay safety and recovery approval. Findings are classified by severity and evidence confidence." |
| 1:14-1:32 | Containment and recovery | "Containment avoids broad retries and unsafe manual repairs. Recovery updates the existing entitlement under locks, verifies access, then repeats the repair to prove no-op and zero additional effects." |
| 1:32-1:47 | Roadmap and CTA | "The responsible first purchase is the $2,250 X-Ray. If evidence supports it, the specimen 30-day rescue sprint is $9,500 across containment, root repair, observability and controlled release. The CTA is a 20-minute Critical Software Risk Triage, not a free architecture audit." |

Do not claim production recovery, client impact, legal assurance, financial assurance or distributed exactly-once delivery.
