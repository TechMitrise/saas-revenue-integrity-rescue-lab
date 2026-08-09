# X-Ray Specimen Technical Note

## Report Data Model

`App\Services\XRayReportService` builds one deterministic report array from the existing Rescue Lab workspace. It includes:

- metadata and specimen boundary;
- executive brief;
- critical-path stages;
- expected/incident/recovered state matrix;
- evidence register;
- deterministic timeline;
- root-cause decision;
- Business Rule Atlas sample;
- prioritised findings;
- containment actions;
- recovery and rollback plan;
- verified outcome;
- priced 30-day roadmap;
- real X-Ray deliverables and CTA.

The service validates internal references before returning the report. Tests cover evidence resolution, stage completeness, deterministic ordering, price totals, verified outcome and idempotency claims.

## Evidence Provenance

Observed facts are derived from the completed synthetic Lab data:

- `lab_runs` for fragile/stabilised measurements;
- `payments`, `subscriptions`, `entitlements`;
- `webhook_events` and `webhook_deliveries`;
- `provisioning_attempts`;
- `incidents`;
- `ReconciliationService` and `PaidAccessRescueTest` for repair/no-op proof.

The specimen does not mutate the live Rescue Lab while rendering. Recovery outcome items are labelled as verified by the existing command/test design when the current fixture is reset to the paid-but-locked state.

## Fact, Inference And Recommendation

- **Observed:** directly supported by database records created by the synthetic fixture.
- **Verified:** supported by existing recovery service, command and tests.
- **Specimen inference:** professional judgement derived from the Lab but not a production finding.
- **Unknown:** requires a real buyer X-Ray with approved client evidence.

The route keeps these categories visible in the evidence register, root-cause decision, findings and roadmap.

## Access-Control Boundary

The specimen is not public proof. Access uses:

- `CRITICAL_XRAY_SPECIMEN_ENABLED` feature flag;
- `CRITICAL_XRAY_ACCESS_CODE` from environment only;
- throttled POST attempts;
- server-side session unlock;
- lock-again action;
- noindex meta and `X-Robots-Tag`;
- robots exclusion for `/critical-software-xray/`.

It is intentionally lighter than enterprise identity management because the data is synthetic. It is still an honest server-side gate, not a client-side password.

## Future Real X-Ray Reports

A real report should reuse this model shape but source evidence from an approved, redacted client evidence package. Do not commit client data, credentials, raw payment payloads or proprietary logs. Use stable evidence IDs, redact identifiers, classify unsupported items as hypotheses or unknowns, and validate every internal reference before sharing.

Reusable proprietary assessment internals are intentionally redacted from this specimen. The buyer sees the deliverable shape and decision quality without receiving the full internal assessment method.
