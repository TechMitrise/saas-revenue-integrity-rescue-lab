# Roadmap

## Guiding Principle

Build the smallest evidence-rich system that helps sell and deliver the Legacy Laravel Rescue service. Do not delay marketing for a broad autonomous modernization platform.

## Phase 0: Protect the Baseline

Status: Ready to begin after commit authorization.

- Review the working tree.
- Create the first local baseline commit.
- Create an additive feature branch.
- Re-run the complete quality gate.

Exit criteria: the current Revenue Integrity product is recoverable and all tests pass.

## Phase 1: Legacy Assessment Foundation

- Add the assessment domain model.
- Add `/app/legacy` without disrupting `/app`.
- Include a clearly labeled representative legacy Laravel fixture.
- Implement the first high-confidence scanner rules.
- Persist findings with evidence, severity, confidence, effort, and business impact.
- Add assessment summary and findings views.
- Add unit, feature, and browser tests.

Exit criteria: a prospect can run a genuine assessment and inspect evidence-backed findings while Revenue Integrity remains unchanged.

## Phase 2: Modernization Proof

- Group findings into stabilization, upgrade, and modernization waves.
- Show dependencies and sequencing.
- Add one real before-and-after transformation.
- Run tests that prove behavior was preserved.
- Add verification results and a downloadable executive report.

Exit criteria: the demonstration proves assessment, modernization reasoning, and verification rather than only workflow management.

## Phase 3: Marketing and Sales Assets

- Build the Legacy Laravel Rescue page in the TMPL website.
- Create the three-minute product and service video.
- Create the Laravel Rescue checklist.
- Create a sample assessment report.
- Finalize discovery questions, outreach copy, and proposal template.
- Add booking and lead capture.

Exit criteria: a prospect can discover the offer, understand it, review proof, and book a call.

## Phase 4: Git, CI, and Deployment

- Configure the approved private Git remote.
- Add CI for Pint, PHPUnit, Vite, and Playwright.
- Select the production host.
- Add only the required production build configuration.
- Provision managed MySQL.
- Configure production secrets and environment variables.
- Deploy migrations and assets.
- Add health checks, logs, monitoring, backups, and rollback instructions.
- Run live desktop and mobile smoke tests.

Exit criteria: the service and application are publicly accessible, monitored, tested, and recoverable.

## Phase 5: Demand-Driven Expansion

Build only in response to validated prospect demand:

- Offline or customer-approved repository assessment workflow
- CSV billing import
- Stripe test-mode connector
- Scheduled scans and notifications
- Expanded scanner rules
- Role-based collaboration
- Assessment comparison over time
- Customer onboarding and commercial SaaS capabilities

## Current Immediate Action

The next code action is Phase 0 followed by Phase 1. Do not start the TMPL marketing implementation until its repository and stack are available.
