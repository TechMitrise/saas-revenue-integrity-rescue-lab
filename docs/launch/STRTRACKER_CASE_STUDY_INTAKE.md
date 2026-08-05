# STRTracker Case-Study Fact Intake

Purpose: collect only the facts needed for a truthful anonymised case study. Do not draft outcome claims until each selected fact has an evidence source and confidentiality status.

## 1. Initial condition

- What did the system or business look like when Mohit became involved?
- What event triggered the work?
- Which statements are directly observed versus remembered?
- Was the work rescue, maintenance, migration, feature delivery or advisory?

## 2. System and stack

- Product type and user group: `[FACT]`
- Languages/frameworks: `[FACT]`
- Database, queue, hosting and integrations: `[FACT]`
- Approximate system age or legacy constraints: `[FACT / UNKNOWN]`
- Which details may be named publicly: `[APPROVED LIST]`

## 3. Actual responsibilities

- Which tasks did Mohit personally own?
- Which tasks were performed jointly or by others?
- What decision authority and production access existed?
- What was explicitly outside responsibility?

## 4. Technical or operational risk

- What failure, bottleneck or uncertainty was present?
- Was impact measured, estimated or only qualitative?
- Which users, workflows or operations were affected?
- What evidence exists: tickets, commits, logs, dashboards, invoices or stakeholder confirmation?

## 5. Actions genuinely performed

- Investigation and diagnosis: `[VERIFIED ACTIONS]`
- Code or schema changes: `[VERIFIED ACTIONS]`
- Deployment or operational work: `[VERIFIED ACTIONS]`
- Documentation, testing or handover: `[VERIFIED ACTIONS]`
- Dates and duration: `[VERIFIED / UNKNOWN]`

## 6. Evidence available

| Evidence | Exists? | Owner | Safe to publish? |
| --- | --- | --- | --- |
| Repository history / commits | `[Y/N]` | `[OWNER]` | `[Y/N/REDACT]` |
| Before/after screenshots | `[Y/N]` | `[OWNER]` | `[Y/N/REDACT]` |
| Test or monitoring results | `[Y/N]` | `[OWNER]` | `[Y/N/REDACT]` |
| Customer or stakeholder quote | `[Y/N]` | `[OWNER]` | `[WRITTEN APPROVAL?]` |
| Commercial or operational metric | `[Y/N]` | `[OWNER]` | `[METHODOLOGY + APPROVAL]` |

## 7. Defensible outcome

- What changed that can be demonstrated?
- Which result can be quantified, and how was it calculated?
- What remained unresolved?
- Was the outcome temporary, sustained or unknown?
- Which wording would overstate causality?

## 8. Confidentiality

- Client/product name: `[PUBLIC / ANONYMISE / DO NOT USE]`
- Industry and geography: `[APPROVED LEVEL]`
- Architecture details to omit: `[LIST]`
- Metrics to omit or round: `[LIST]`
- Required approval contact: `[NAME / ROLE]`
- Approval record location: `[PATH / EMAIL / UNKNOWN]`

## Publication gate

Do not publish until responsibility, evidence, outcome wording and confidentiality are each approved. Unknown facts stay omitted; they are never filled with plausible estimates.
