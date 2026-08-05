# Product Scope

## Product Definition

Rescue Lab is a delivery and evidence platform for the Legacy Laravel Rescue service. It should demonstrate how business-critical failures are identified, prioritized, rescued, modernized, and verified.

It is not positioned as a generic project-management tool or an automatic code-upgrade product.

## Brand Architecture

```text
TMPL legacy-modernization brand
  -> Legacy Laravel Rescue
      -> Rescue Lab
          -> Revenue Integrity
          -> Legacy Assessment
          -> Modernization Plan
          -> Verification Center
```

## Implemented Scope: Revenue Integrity

The implemented module demonstrates:

- Normalized SaaS billing data
- Deterministic anomaly detection
- Revenue exposure calculation
- Prioritized incident operations
- Ownership and investigation notes
- Recovery measurement
- Audit history and export
- Tenant-safe Laravel architecture

The module proves the team's ability to translate a business risk into a modern, tested Laravel workflow.

## Proposed Scope: Legacy Assessment

The assessment module should support one credible golden path:

1. Select a representative legacy Laravel project.
2. Run a genuine static assessment.
3. Identify framework, dependency, architecture, database, queue, security, testing, and deployment risks.
4. Show file-level evidence.
5. Map each technical finding to business impact.
6. Prioritize findings by severity, confidence, and effort.
7. Generate stabilization, upgrade, and modernization waves.
8. Show at least one verified before-and-after improvement.
9. Export an executive assessment report.

Initial scanner scope:

- Laravel and PHP compatibility
- Composer dependency health
- Oversized controllers and models
- Business logic embedded in controllers
- Raw SQL and likely query risks
- Route closures and coupling
- Queue retries, timeouts, and failure handling
- Scheduler and webhook reliability
- Authentication and authorization signals
- Test presence and coverage indicators
- Deployment and environment risks
- Revenue-critical workflow signals

## Deliberate Non-Goals Before Market Validation

- One-click automatic Laravel upgrades
- Unreviewed AI-generated code changes
- Direct GitHub repository access
- Support for every PHP framework
- Full SaaS subscription billing for Rescue Lab
- A large catalog of shallow scanner checks
- Claims of complete security or compliance auditing

## Prospect Deliverables

The service should ultimately produce:

- Codebase health summary
- Business-risk map
- Evidence-backed findings
- Quick-win recommendations
- Incremental modernization roadmap
- Upgrade sequence and dependencies
- Test and verification strategy
- Effort and risk ranges
- Executive assessment report
- Optional Rescue Sprint proposal

## Completion Definitions

### Market-Ready Demonstration

- Public TMPL service page
- Deployed Rescue Lab
- Revenue Integrity demonstration
- Legacy Assessment demonstration
- Sample report
- Three-minute video
- Booking CTA
- Tested prospect talk track

### Commercial SaaS

Commercial SaaS requires additional work such as self-service onboarding, customer data ingestion, stronger role management, subscriptions, monitoring, support processes, legal documents, and production operations. It is intentionally separate from the market-ready service demonstration.
