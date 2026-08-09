# Three-Minute Live Demo Path

Use this during a call with a qualified founder, CTO, product leader or engineering leader.

## Setup

1. Run `make demo`.
2. Configure `CRITICAL_XRAY_SPECIMEN_ENABLED=true`, `CRITICAL_XRAY_ACCESS_CODE` and `TRIAGE_CTA_URL`.
3. Clear config/cache.
4. Open `/critical-software-xray/sample/revenue-access-failure/access`.
5. Unlock with the configured access code.

## Demo Flow

| Time | Action | Narration |
| ---: | --- | --- |
| 0:00-0:25 | Show hero and boundary | "This is not another product dashboard. It is the specimen of a paid X-Ray deliverable: synthetic, protected and evidence-backed." |
| 0:25-0:55 | Guided view step 1-2 | "The buyer understands the commercial failure first: paid customer, locked access, no unsupported claim about wider exposure." |
| 0:55-1:20 | Critical-path map, click Queue / job | "Here is where the path first diverges. Payment and webhook idempotency held. Queue fulfilment failed three bounded attempts." |
| 1:20-1:45 | Expected vs Actual and Timeline | "Every statement links to evidence IDs. Expected, incident and recovered states stay separate." |
| 1:45-2:10 | Findings and Business Rules | "This is where TMPL separates confirmed defect, systemic control gap, unverified risk and improvement opportunity." |
| 2:10-2:35 | Containment and Recovery | "The response is bounded: do not rewrite, do not replay everything, do not create duplicate entitlements. Repair the existing entitlement and verify repeat no-op." |
| 2:35-3:00 | Roadmap and CTA | "A real buyer starts with the Critical Software X-Ray. The specimen sprint is priced at $9,500 only after the evidence supports the rescue path." |

## Qualification Questions

1. Which critical workflow is failing, fragile or too risky to change?
2. What customer or revenue consequence occurs when it breaks?
3. What evidence joins the business event to the technical state?
4. Who can approve containment and recovery decisions?
5. What would make a rewrite or AI automation unsafe before evidence is gathered?

End on the primary CTA: **Request a 20-minute Critical Software Risk Triage**.
