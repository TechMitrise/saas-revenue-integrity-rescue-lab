# Critical Software X-Ray Specimen

This directory contains the protected buyer specimen for the **Critical Software X-Ray**. It turns the completed SaaS Revenue Integrity Rescue Lab incident into a professional-service sample: what TMPL examines, how evidence is separated from assumptions, where the workflow diverged, and what a priced rescue sequence would deliver.

Boundary: this is a **synthetic professional-service specimen—not a client case study or production monitoring product**. It uses fictional Northstar Labs evidence from the Rescue Lab and does not claim real customer outcomes, revenue saved, legal/compliance assurance or live monitoring.

## Route

Local route:

```text
http://localhost:18080/critical-software-xray/sample/revenue-access-failure
```

Access gate:

```text
http://localhost:18080/critical-software-xray/sample/revenue-access-failure/access
```

## Configuration

Add these environment values locally or in an approved isolated deployment:

```dotenv
CRITICAL_XRAY_SPECIMEN_ENABLED=true
CRITICAL_XRAY_ACCESS_CODE="share-a-real-code-outside-git"
TRIAGE_CTA_URL="https://approved-booking-or-contact-url.example"
```

The access code is never committed. The specimen is server-side protected, session-based after unlock, rate limited on access attempts, and can be locked again from the page header.

If `TRIAGE_CTA_URL` is absent, the app falls back to `RESCUE_LAB_CONTACT_URL`. Do not invent a booking URL.

## Local Run

```bash
make demo
./vendor/bin/sail artisan lab:reset
```

Then set the X-Ray env values in `.env` and clear config/cache:

```bash
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
```

## Generate Assets

The capture command needs the same access code in the runtime environment:

```bash
CRITICAL_XRAY_ACCESS_CODE="share-a-real-code-outside-git" make xray-assets
```

Outputs:

- `docs/xray-specimen/critical-software-xray-specimen.pdf`
- `docs/xray-specimen/screenshots/01-executive-risk-brief.png`
- `docs/xray-specimen/screenshots/02-critical-path-divergence.png`
- `docs/xray-specimen/screenshots/03-expected-vs-actual-state.png`
- `docs/xray-specimen/screenshots/04-recovery-rollback-decision.png`
- `docs/xray-specimen/screenshots/05-priced-30-day-roadmap.png`

## Verification

Run:

```bash
make test
./vendor/bin/sail composer audit --locked --no-interaction
./vendor/bin/sail npm audit --audit-level=low
./vendor/bin/sail composer validate --strict --no-check-publish
./vendor/bin/sail composer check-platform-reqs --lock
```

Also verify the scenario:

```bash
./vendor/bin/sail artisan lab:simulate
./vendor/bin/sail artisan lab:reconcile --verify-no-op
./vendor/bin/sail artisan lab:reset
```

## Deployment Readiness

- Keep `CRITICAL_XRAY_SPECIMEN_ENABLED=false` unless the specimen is intentionally shared.
- Set a unique access code through the deployment secret manager.
- Set `TRIAGE_CTA_URL` or `RESCUE_LAB_CONTACT_URL`.
- The route returns `X-Robots-Tag: noindex, nofollow`, includes noindex meta tags, and `robots.txt` disallows `/critical-software-xray/`.
- Do not enable demo mutations on a public production deployment unless it is an isolated synthetic lab.
- Rollback is normal application rollback: disable `CRITICAL_XRAY_SPECIMEN_ENABLED`, clear config, and redeploy the previous artifact if needed.

## Related Files

- [Technical note](TECHNICAL_NOTE.md)
- [90-120 second walkthrough script](WALKTHROUGH_SCRIPT.md)
- [Three-minute live-demo path](THREE_MINUTE_DEMO_PATH.md)
