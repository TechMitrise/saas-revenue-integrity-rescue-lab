# Decisions

1. The public product demonstrates only the synthetic paid-but-locked revenue path.
2. Delivery is described as at least once; only the business effect is effectively once.
3. MySQL constraints and transactions are the final consistency boundary.
4. Valkey and Horizon provide real queue infrastructure; application attempt records retain domain evidence.
5. Reconciliation updates the existing entitlement under locks and writes an immutable action for every run.
6. Fragile and stabilised modes use the same synthetic scenario key and remain reproducible.
7. No source push, deployment, real provider, customer data, or real revenue claim is authorised.
