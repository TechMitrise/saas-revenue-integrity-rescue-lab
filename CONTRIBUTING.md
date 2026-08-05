# Contributing

Contributions should preserve the lab's narrow evidence claim: one synthetic paid-but-locked incident, traced and reconciled safely.

## Local workflow

```bash
make demo
make test
```

Open `http://localhost:18080/proof` for the public proof page and `http://localhost:18080/app` for the authenticated lab.

## Pull requests

- Keep all data and provider interactions synthetic.
- Do not add claims of distributed exactly-once delivery.
- Add or update tests for changed behaviour.
- Run `make test` before requesting review.
- Update architecture or operations evidence when a control changes.
- Do not commit `.env`, generated media, dependencies, logs or test artifacts.

Use concise commits with an imperative subject. Security concerns belong in private vulnerability reporting, not public issues.
