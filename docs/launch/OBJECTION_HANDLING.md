# Objection Handling

## "This is synthetic. How does it prove client impact?"

It does not claim client impact. It proves the investigation and recovery method is executable: persisted deliveries, queue attempts, database constraints, before/after evidence and repeat-no-op tests. Your Audit establishes facts in your own approved evidence.

## "This looks like a small CRUD demo."

The value is not the forms. The lab demonstrates cross-boundary failure handling: signed at-least-once delivery, event identity, queued fulfilment, SLA detection, transactional reconciliation and recurrence controls. The UI exists to make those states inspectable.

## "Can you guarantee exactly-once processing?"

No distributed exactly-once claim is made. The accurate guarantee is at-least-once event delivery with an idempotent, effectively-once business effect, supported by unique identities, guarded transitions and repeat-safe recovery.

## "We can just retry the failed job."

A retry may be correct, but only after confirming event identity, current entitlement state, previous effects and repeat safety. The Audit identifies when retry is safe and when reconciliation needs stronger constraints.

## "We need a full rewrite."

That may eventually be justified, but a rewrite should not be the first response to an unbounded critical path. The Audit separates immediate revenue-path stabilisation from longer modernisation decisions.

## "We cannot share our repository or production data."

The first call requires neither. The Audit can start with architecture, schemas, redacted evidence and reviewed screen-share. Any access is least-privilege, scoped and revocable. No production mutation permission is requested.

## "Why pay USD 1,500-3,000 before implementation?"

The fee buys a decision-quality path map, ranked evidence and acceptance criteria before production changes. It reduces the risk of patching symptoms, duplicating effects or estimating a rewrite against an unverified cause.

## "Can our team implement the recommendations?"

Yes. The deliverables are designed to stand alone. A Stabilisation Sprint is optional and separately scoped.

## "Can you review the whole codebase in the same five days?"

No. The Audit is useful because it is constrained to one revenue-critical path. Broad review would weaken evidence depth and requires separate scope.
