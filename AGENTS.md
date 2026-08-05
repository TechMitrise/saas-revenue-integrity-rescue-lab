# Project Instructions

## Purpose

This repository contains the SaaS Revenue Integrity Rescue Lab, a Laravel and MySQL application used to demonstrate revenue-leak detection, operational rescue workflows, and the broader Legacy Laravel Rescue service.

Read these files before planning or changing the project:

1. `docs/CURRENT_STATE.md`
2. `docs/PRODUCT_SCOPE.md`
3. `docs/DECISIONS.md`
4. `docs/ROADMAP.md`
5. `docs/PROSPECT_DEMO.md`
6. `docs/GROWTH_ENGINE.md`
7. `docs/ARCHITECTURE.md`
8. `docs/OPERATIONS.md`

## Non-Negotiable Constraints

- Preserve the existing Revenue Integrity module and its tested behavior.
- Make the proposed Legacy Laravel Assessment module additive; do not replace the current product.
- Do not configure a GitHub remote, push code, or use repository credentials without explicit user authorization.
- Do not deploy or connect external services without explicit user authorization.
- Keep the public TMPL marketing website separate from this application unless its repository and stack are provided.
- Do not imply that the current application connects to Stripe, Chargebee, Recurly, or customer production data.
- Do not claim that marking an incident recovered moves money or repairs the billing source. It records an operational outcome.
- Do not expose seeded demo credentials or reset functionality outside local/demo-safe environments.
- Use additive migrations. Do not rewrite applied production migrations after deployment begins.
- Preserve tenant scoping on every read and mutation.

## Current Technical Baseline

- Laravel 13.8 with framework 13.x
- PHP 8.5 through Laravel Sail
- MySQL 8.4
- Blade and Vite
- PHPUnit and Playwright
- Local application: `http://localhost:18080`
- Local forwarded MySQL: `localhost:13307`

The host does not require PHP, Composer, or MySQL. Run project commands through `./vendor/bin/sail`.

## Required Quality Gate

Run these checks after material changes:

```bash
./vendor/bin/sail pint --test
./vendor/bin/sail test
./vendor/bin/sail npm run build
./vendor/bin/sail npm run test:e2e
```

The existing baseline is 10 passing PHPUnit tests with 50 assertions and two passing Playwright workflows covering desktop and mobile Chromium.

## Strategic Direction

The product strategy is service-first:

```text
TMPL legacy-modernization brand
  -> Legacy Laravel Rescue service
  -> Rescue Lab delivery platform
      -> Revenue Integrity module (implemented)
      -> Legacy Assessment module (proposed)
      -> Modernization Plan module (proposed)
      -> Verification Center (proposed)
```

The next highest-value product work is a narrow, credible Legacy Laravel assessment workflow with real evidence, not a broad automatic code-upgrade platform.

## Change Discipline

- Inspect the working tree before editing.
- Create a local baseline commit before beginning the Legacy Assessment expansion, after receiving authorization to commit.
- Keep `/app` working while adding new functionality.
- Prefer a small number of high-quality scanner rules with file-level evidence.
- Label sample codebases, simulated integrations, and representative findings honestly.
- Update `docs/CURRENT_STATE.md`, `docs/DECISIONS.md`, and `docs/ROADMAP.md` when the product state changes.
