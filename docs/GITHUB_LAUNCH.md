# GitHub Launch Runbook

No remote or credentials are configured by this runbook. Replace every `<github-owner>` placeholder only after the destination is approved.

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

Set `RESCUE_LAB_REPOSITORY_URL` in the deployed environment after the repository exists so the proof page displays its GitHub link.

Set `RESCUE_LAB_VIDEO_URL` to the approved 90-120-second walkthrough and `RESCUE_LAB_CONTACT_URL` to the approved Risk Triage form or scheduling page. Until those values exist, keep the visible placeholders in the WordPress copy rather than guessing destinations.

Production defaults to a read-only proof page. Set `RESCUE_LAB_DEMO_MUTATIONS_ENABLED=true` only when the deployment is an isolated synthetic lab and authenticated visitors should be able to replay/reset its fixture.

Verify no namespace placeholder has escaped this runbook:

```bash
rg -n "<github-owner>" . --glob '!docs/GITHUB_LAUNCH.md'
```

## Remote and first push

Run these only after credentials and the empty repository are provided:

```bash
git remote add origin git@github.com:<github-owner>/saas-revenue-integrity-rescue-lab.git
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

After CI is green, replace the placeholder links and create an annotated release from the tested commit:

```bash
git tag -a v1.0.0 -m "SaaS Revenue Integrity Rescue Lab v1.0.0"
git push origin v1.0.0
```

Release title: `v1.0.0 - Paid-but-locked rescue proof`

Release summary:

> An executable Laravel/MySQL demonstration of a revenue-critical SaaS incident: ten webhook deliveries become one provider event and one business effect, entitlement provisioning fails three times, an SLA audit identifies USD 4,800 of synthetic exposure, and transactional reconciliation repairs access once before proving a repeat no-op.

Attach the prospect video only if GitHub release storage is the approved distribution channel. Generated media remains ignored by default.
