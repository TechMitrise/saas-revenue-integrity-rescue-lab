# EOD Launch Control Center

This directory is the operating package for launching the SaaS Revenue Integrity Rescue Lab without changing product scope.

## Launch position

| Layer | Single message |
| --- | --- |
| Problem | A successful payment can still become churn when access fails afterward. |
| Proof | The lab reproduces, traces and safely reconciles one paid-but-locked Laravel SaaS incident. |
| Capability | Revenue-path failures can be made observable, bounded and recoverable without a risky rewrite. |
| Offer | Legacy SaaS Risk & Revenue Audit: a fixed-scope assessment that maps one critical path and returns evidence plus a prioritised rescue plan. |
| Next step | A short fit call, followed by a fixed-fee audit proposal. |

## Package map

| Moment | Asset | Use |
| --- | --- | --- |
| Public discovery | [`/proof`](../../resources/views/proof.blade.php) | Establish business relevance and engineering credibility. |
| WordPress publishing | [`WEBSITE_PROOF_COPY.md`](WEBSITE_PROOF_COPY.md) | Paste the complete proof structure with visible URL placeholders. |
| GitHub evaluation | [`README.md`](../../README.md), [`GITHUB_LAUNCH.md`](../GITHUB_LAUNCH.md) | Help a technical buyer validate setup, scope and release evidence. |
| Async walkthrough | [`WALKTHROUGH.md`](WALKTHROUGH.md), `make video` | Send the 105-second proof or record a narrated version. |
| Evidence screenshots | [`SCREENSHOT_INDEX.md`](SCREENSHOT_INDEX.md) | Use the generated application frames without cropping out synthetic disclosure. |
| Scenario study | [`SCENARIO_STUDY.md`](../SCENARIO_STUDY.md) | Explain the fictional business context, evidence inspected and verified Lab result. |
| X-Ray sample | [`CRITICAL_SOFTWARE_XRAY_SAMPLE.md`](../CRITICAL_SOFTWARE_XRAY_SAMPLE.md) | Show what a paid diagnostic would provide using the Lab evidence standard. |
| Three-minute path | [`THREE_MINUTE_SALES_DEMO.md`](../THREE_MINUTE_SALES_DEMO.md) | Run the short sales demo and transition to the paid X-Ray. |
| Live call | [`DEMO_CALL_RUNBOOK.md`](DEMO_CALL_RUNBOOK.md) | Run discovery, proof, relevance mapping and close. |
| Objections | [`OBJECTION_HANDLING.md`](OBJECTION_HANDLING.md) | Answer scope, synthetic-data, rewrite and exactly-once concerns accurately. |
| Paid entry offer | [`AUDIT_OFFER.md`](AUDIT_OFFER.md) | Explain who the audit is for, what ships and what is excluded. |
| Qualification | [`AUDIT_INTAKE.md`](AUDIT_INTAKE.md) | Collect the minimum safe context before access is requested. |
| Commercial close | [`PROPOSAL_TEMPLATE.md`](PROPOSAL_TEMPLATE.md), [`PROPOSAL_VARIANTS.md`](PROPOSAL_VARIANTS.md) | Produce a client-specific proposal from one of four job triggers. |
| Upwork | [`UPWORK_PORTFOLIO.md`](UPWORK_PORTFOLIO.md) | Publish the title, description, tags, images, captions and CTA. |
| Direct outreach | [`OUTREACH_KIT.md`](OUTREACH_KIT.md) | Contact founders, CTOs, partners and relevant job posters from observable triggers. |
| Same-day follow-up | [`FOLLOW_UP_TEMPLATES.md`](FOLLOW_UP_TEMPLATES.md) | Send proof, recap, proposal and no-show messages. |
| Truthful case study | [`STRTRACKER_CASE_STUDY_INTAKE.md`](STRTRACKER_CASE_STUDY_INTAKE.md) | Gather defensible STRTracker facts without inventing outcomes. |
| Release control | [`FREEZE_MANIFEST.md`](FREEZE_MANIFEST.md) | Preserve tested scope, boundaries and launch gates. |
| Quality review | [`QUALITY_SCORECARD.md`](QUALITY_SCORECARD.md) | Record weaknesses, corrections, final scores and evidence. |

## EOD launch sequence

1. Run `make test`, both dependency audits and `make proof-assets`.
2. Confirm `git status --short` is empty and the local release tag points to the tested commit.
3. Confirm the approved TechMitrise repository URL still resolves publicly.
4. Push `main`, wait for both hosted CI jobs, then publish the release and social preview.
5. Deploy `/proof` in read-only mode first. Enable synthetic demo mutations only on an isolated lab.
6. Configure `RESCUE_LAB_REPOSITORY_URL` and `RESCUE_LAB_CONTACT_URL` on the approved host.
7. Send the 105-second walkthrough before a call, not the proposal.
8. Use the call to diagnose relevance, demonstrate evidence and offer the Audit only when fit is confirmed.

## Freeze rule

After the local release tag is created, do not add product features. Only these changes are allowed before launch:

- a release-blocking security or correctness fix;
- destination-specific URLs and approved contact details;
- typo or claim-boundary corrections;
- generated screenshots or video from the frozen application.

Any behaviour change reopens the release gate and requires a new candidate tag.

## External inputs still required

- final GitHub owner and credentials;
- approved proof-page domain and hosting target;
- approved contact or scheduling URL;
- approved public walkthrough video URL;
- client-specific fee, legal entity, payment terms and jurisdiction for proposals.

No placeholder above should be guessed during launch.
