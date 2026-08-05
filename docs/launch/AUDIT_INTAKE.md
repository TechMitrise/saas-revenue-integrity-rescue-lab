# Legacy SaaS Risk & Revenue Audit Intake

Complete this before repository, log or database access is requested. Do not paste secrets, card data, customer personal data or production tokens into this document.

## Business path

1. What customer action creates the charge or successful payment?
2. What value should the customer receive afterward: access, seats, credits, upgrade, renewal or another entitlement?
3. What is the expected payment-to-value SLA?
4. Which customer segment or revenue band is most sensitive to delay?
5. What is the business impact when the path fails?

## Technical path

6. Which payment provider and event types start fulfilment?
7. Where are provider deliveries and logical events persisted?
8. Which queues, jobs or external services complete provisioning?
9. Which identifiers correlate payment, subscription, account and entitlement?
10. Which database constraints prevent duplicate effects?
11. What retry, timeout and dead-letter behaviour is configured?
12. How is access reconciled when automation fails?

## Current evidence

13. Provide one sanitized incident timeline or representative failure description.
14. Which dashboards, logs, failed-job records or support tags are available?
15. Which repair steps are currently manual, and who can run them?
16. Has a replay or retry ever created duplicate access, credits or notifications?
17. Which changes to this path are already planned or frozen?

## Access boundary

| Access | Preferred condition |
| --- | --- |
| Source | Read-only, scoped repository or reviewed screen-share |
| Logs | Redacted export limited to the selected correlation trail |
| Database | Schema first; read-only sampled evidence only when necessary |
| Provider | Documentation and sanitized payload shape, never live credentials |
| Production | No mutation permission and no audit tooling installed without approval |

## Named participants

- Executive or revenue owner: `[NAME / ROLE]`
- Engineering owner: `[NAME / ROLE]`
- Operations/support owner: `[NAME / ROLE]`
- Security/access approver: `[NAME / ROLE]`

## Scope confirmation

- Selected revenue path: `[PATH]`
- Audit start: `[DATE]`
- Readout target: `[DATE]`
- Approved evidence locations: `[LIST]`
- Explicitly excluded systems: `[LIST]`
