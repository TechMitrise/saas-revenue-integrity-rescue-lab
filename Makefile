.PHONY: demo reset simulate reconcile test video screenshots proof-assets xray-assets

.env:
	cp .env.example .env

vendor/.rescue-lab-composer-ready: composer.lock composer.json
	docker run --rm -v "$(CURDIR):/app" -w /app composer:2 composer install --no-interaction --ignore-platform-req=ext-pcntl
	touch vendor/.rescue-lab-composer-ready

demo: .env vendor/.rescue-lab-composer-ready
	docker compose up -d --build
	./vendor/bin/sail artisan lab:setup
	./vendor/bin/sail npm ci --ignore-scripts
	./vendor/bin/sail npx playwright install chromium
	./vendor/bin/sail npm run build

reset:
	./vendor/bin/sail artisan lab:reset

simulate:
	./vendor/bin/sail artisan lab:simulate

reconcile:
	./vendor/bin/sail artisan lab:reconcile --verify-no-op

test:
	./vendor/bin/sail pint --test
	./vendor/bin/sail npm run build
	./vendor/bin/sail test
	./vendor/bin/sail artisan cache:clear
	./vendor/bin/sail npm run test:e2e

video: demo
	./vendor/bin/sail artisan cache:clear
	./vendor/bin/sail npm run record:demo
	./vendor/bin/sail artisan lab:reset

screenshots:
	./vendor/bin/sail npm run build
	./vendor/bin/sail artisan cache:clear
	./vendor/bin/sail npm run capture:demo-screenshots

proof-assets: screenshots
	./vendor/bin/sail npm run capture:proof

xray-assets:
	./vendor/bin/sail npm run build
	./vendor/bin/sail artisan cache:clear
	./vendor/bin/sail npm run capture:xray
