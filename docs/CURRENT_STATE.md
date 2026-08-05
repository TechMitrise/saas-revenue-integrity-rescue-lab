# Current State

Last reviewed: August 5, 2026

## Implemented

- Laravel 13/PHP 8.5 tenant-aware application with MySQL 8.4
- Synthetic organisation, plan, subscription, successful payment, and entitlement
- Signed webhook endpoint, persistent inbox, and delivery ledger
- Unique provider event/delivery and entitlement constraints
- Idempotent event consumer and safe subscription transitions
- Valkey-compatible queue, Horizon, and bounded provisioning job
- Deterministic retry exhaustion and paid-but-locked SLA incident
- Revenue exposure using the paid monthly subscription amount
- Lock-protected reconciliation and immutable before/after audit
- Verified repeat reconciliation no-op
- Stored fragile-versus-stabilised evidence and measurements
- Desktop/mobile Control Room journey
- PHPUnit, Playwright, formatting, build, dependency audits, and GitHub Actions
- One-command local setup and scenario operations

## Deliberate limits

- Synthetic provider and deterministic failure adapter only
- No real customer, payment, entitlement, or recovered-revenue claim
- No public signup or commercial billing
- No production deployment or external telemetry
- No distributed exactly-once guarantee

The accurate guarantee is at-least-once event delivery with an idempotent, effectively-once business effect.
