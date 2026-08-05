# Security Policy

## Supported version

Security fixes are applied to the latest commit on `main`. This repository is a synthetic demonstration and is not a production billing service.

## Reporting a vulnerability

Do not open a public issue for a suspected vulnerability. Use GitHub's private vulnerability reporting feature after the repository is published. Include:

- the affected route, component or dependency;
- reproduction steps using synthetic data only;
- the expected and observed security boundary;
- any suggested mitigation.

Until private reporting is enabled, contact the repository owner through the private channel listed on their GitHub profile. Do not include credentials, customer data or active exploit payloads.

## Demonstration boundary

- All identities, payments, failures, timings and revenue figures are synthetic.
- Local demonstration credentials are not production credentials.
- Synthetic webhook, audit, reconciliation, reset and guided endpoints return `404` unless `RESCUE_LAB_DEMO_MUTATIONS_ENABLED=true`.
- Production defaults to read-only proof. Enable mutations only for an isolated synthetic demonstration workspace.
- A public deployment requires separate secrets, TLS, monitoring, backups and an operator access policy.
