# GitHub Launch Runbook

The approved public destination is `https://github.com/TechMitrise/saas-revenue-integrity-rescue-lab`. Local `origin` is configured without credentials; no push or deployment has been performed.

## Repository settings

| Field | Launch value |
| --- | --- |
| Name | `saas-revenue-integrity-rescue-lab` |
| Visibility | Public, after the synthetic-data and MIT-license decision is confirmed |
| Description | Executable Laravel proof of paid-but-locked detection, duplicate-safe webhooks and safe entitlement reconciliation. |
| Website | Public `/proof` deployment URL |
| Topics | `laravel`, `php`, `mysql`, `saas`, `webhooks`, `idempotency`, `queues`, `revenue-integrity`, `legacy-modernization` |
| Default branch | `main` |

Upload `docs/assets/github-social-preview.png` under **Settings > General > Social preview**.

## Repository link

The committed default for `RESCUE_LAB_REPOSITORY_URL` is `https://github.com/TechMitrise/saas-revenue-integrity-rescue-lab`, so the proof page displays the approved GitHub link. An environment-specific value may override it if the repository moves.

Set `RESCUE_LAB_VIDEO_URL` to the approved 90-120-second walkthrough and `RESCUE_LAB_CONTACT_URL` to the approved Risk Triage form or scheduling page. Until those values exist, keep the visible placeholders in the WordPress copy rather than guessing destinations.

Production defaults to a read-only proof page. Set `RESCUE_LAB_DEMO_MUTATIONS_ENABLED=true` only when the deployment is an isolated synthetic lab and authenticated visitors should be able to replay/reset its fixture.

Verify the approved destination is present:

```bash
rg -n "TechMitrise/saas-revenue-integrity-rescue-lab" .
```

## Remote and first push

Run this only after Mohit authenticates locally and explicitly approves the push:

```bash
git push -u origin main
```

No force push is required. Confirm the CI workflow passes before creating a release.

## Protection and security

- Require a pull request and the `backend-and-build` and `browser` checks before merging to `main`.
- Block force pushes and branch deletion.
- Enable secret scanning, push protection, Dependabot alerts and private vulnerability reporting.
- Keep workflow permissions read-only unless a future job proves it needs more.
- Do not configure deployment secrets until an approved hosting plan exists.

## First release

After CI is green, publish the existing local annotated tag from the tested commit:

```bash
git push origin v1.0.0
```

Release title: `v1.0.0 - Paid-but-locked rescue proof`

Release summary:

> An executable Laravel/MySQL demonstration of a revenue-critical SaaS incident: ten webhook deliveries become one provider event and one business effect, entitlement provisioning fails three times, an SLA audit identifies USD 4,800 of synthetic exposure, and transactional reconciliation repairs access once before proving a repeat no-op.

Attach the prospect video only if GitHub release storage is the approved distribution channel. Generated media remains ignored by default.
