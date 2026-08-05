# Rescue Lab Freeze Manifest

Freeze date: 2026-08-05  
Release tag: `v1.0.0`  
Scope: SaaS Revenue Integrity Rescue Lab and launch collateral

## Frozen product behaviour

- Laravel/PHP and MySQL implementation of one synthetic paid-but-locked incident.
- Ten signed webhook deliveries retained as one provider event and one business effect.
- Three persisted entitlement-provisioning failures with bounded retries.
- SLA-based detection of one account and USD 4,800 synthetic monthly exposure.
- Transactional reconciliation that repairs once and records a repeat no-op.
- Fragile-versus-stabilised evidence, guided replay and executable engineering references.
- Public proof page with production read-only default and explicit isolated-demo mutation opt-in.

The accurate guarantee remains:

> At-least-once event delivery with an idempotent, effectively-once business effect.

## Frozen exclusions

- no real payment provider or customer data;
- no distributed exactly-once claim;
- no public signup, commercial billing or production identity integration;
- no claim of actual recovered customer revenue;
- no production deployment, domain, remote push or telemetry configuration.

## Required freeze gates

| Gate | Required result |
| --- | --- |
| `make test` | Pint, production build, all PHP tests and both Playwright projects pass |
| `make proof-assets` | README screenshot and 1280 x 640 social preview regenerate |
| `composer validate --strict --no-check-publish` | Valid |
| `composer audit --locked --no-interaction` | No advisories |
| `npm audit --audit-level=low` | Zero vulnerabilities |
| GitHub YAML parse | All workflow and template YAML parses |
| Secret-pattern scan | No common credential patterns in tracked launch sources |
| `git diff --check` | No whitespace errors |
| Fixture state | Active paid-but-locked scenario restored |

Observed results are recorded in [`../RELEASE_AUDIT.md`](../RELEASE_AUDIT.md).

## Change control

The `v1.0.0` tag marks the local launch freeze after final gates pass. Any source change after that tag must be classified as:

1. release blocker, requiring tests and a new patch tag;
2. destination configuration, applied through environment values rather than source edits;
3. post-launch backlog, not included in the EOD launch.

Do not amend or force-move the freeze tag after publication.
