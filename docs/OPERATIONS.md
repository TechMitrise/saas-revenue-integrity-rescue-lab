# Operations

## Daily commands

Start the stack:

```bash
./vendor/bin/sail up -d
```

View service state and logs:

```bash
./vendor/bin/sail ps
./vendor/bin/sail logs
```

Stop containers without deleting data:

```bash
./vendor/bin/sail stop
```

MySQL data persists in the `sail-mysql` Docker volume across stops and rebuilds.

## Database lifecycle

Apply pending migrations without deleting data:

```bash
./vendor/bin/sail artisan migrate
```

Recreate the local database and original rescue scenario:

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

The dashboard's `Reset lab` action performs a workspace-level scenario reset and is intentionally available only when `APP_ENV` is `local` or `testing`.

## Quality gates

Run these checks before a release candidate:

```bash
./vendor/bin/sail pint --test
./vendor/bin/sail test
./vendor/bin/sail npm run build
./vendor/bin/sail npm run test:e2e
```

PHPUnit uses the MySQL `testing` database created by Sail. `RefreshDatabase` isolates each test. Playwright runs the full workflow at desktop and mobile viewports against the running application container using a pinned Chromium build installed with:

```bash
./vendor/bin/sail npx playwright install chromium
```

Failed Playwright runs retain traces and screenshots under ignored local artifact directories.

## Ports

| Service | Host | Container |
| --- | ---: | ---: |
| Laravel | `18080` | `80` |
| MySQL | `13307` | `3306` |
| Vite development server | `5173` | `5173` |

Change host bindings with `APP_PORT`, `FORWARD_DB_PORT`, and `VITE_PORT` in `.env` if another local project uses them. Laravel must continue to use `DB_HOST=mysql` and `DB_PORT=3306` inside Sail.

## Troubleshooting

If Sail reports that Docker is unavailable, start Docker Desktop and retry. If a port is occupied, change only the corresponding host variable in `.env`.

If the app starts before MySQL is healthy, wait a few seconds and run:

```bash
./vendor/bin/sail artisan migrate:status
```

If compiled assets are missing, rebuild them:

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

## Production guardrails

Before any deployment, set `APP_ENV=production`, `APP_DEBUG=false`, a production `APP_URL`, a unique `APP_KEY`, and managed database credentials. Run migrations with `--force`, compile assets in CI, and never run `migrate:fresh`, `db:seed`, or the demo reset against production data.

The local demo credentials are prefilled only in the local environment. Production login fields remain empty. Replace the seeded operator with the intended identity and access-management process before exposing the application.

No GitHub remote, external billing provider, email provider, or telemetry service is configured by this project.
