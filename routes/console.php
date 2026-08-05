<?php

use App\Models\Incident;
use App\Models\LabRun;
use App\Models\User;
use App\Models\Workspace;
use App\Services\ReconciliationService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoScenarioSeeder;
use Illuminate\Support\Facades\Artisan;

Artisan::command('lab:setup', function (): int {
    if (blank(config('app.key'))) {
        Artisan::call('key:generate', ['--force' => true]);
        $this->info('Generated the missing local APP_KEY.');
    }

    Artisan::call('migrate', ['--force' => true]);
    $workspace = Workspace::query()->where('slug', 'rescue-lab')->first();

    if (! $workspace || ! User::query()->where('email', 'operator@rescuelab.test')->exists()) {
        Artisan::call('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true]);
    } elseif (! LabRun::query()->where('workspace_id', $workspace->id)->exists()) {
        resolve(DemoScenarioSeeder::class)->seed($workspace);
    }

    $this->info('Rescue Lab is ready at '.config('app.url').'.');

    return self::SUCCESS;
})->purpose('Idempotently prepare the local Rescue Lab without rotating an existing APP_KEY');

Artisan::command('lab:reset', function (): int {
    $workspace = Workspace::query()->where('slug', 'rescue-lab')->firstOrFail();
    resolve(DemoScenarioSeeder::class)->seed($workspace);
    $this->info('Synthetic paid-access scenario reset.');

    return self::SUCCESS;
})->purpose('Reset all synthetic scenario evidence to the paid-but-locked starting point');

Artisan::command('lab:simulate', function (): int {
    $workspace = Workspace::query()->where('slug', 'rescue-lab')->firstOrFail();
    resolve(DemoScenarioSeeder::class)->seed($workspace);
    $this->table(
        ['Mode', 'Deliveries', 'Business actions', 'Queue failures', 'Access', 'SLA breached'],
        LabRun::query()->whereBelongsTo($workspace)->get()->map(fn (LabRun $run): array => [
            $run->mode,
            $run->webhook_deliveries,
            $run->business_actions,
            $run->queue_failures,
            $run->entitlement_state,
            $run->sla_breached ? 'yes' : 'no',
        ])->all(),
    );

    return self::SUCCESS;
})->purpose('Replay the fragile and stabilised synthetic payment-to-access paths');

Artisan::command('lab:reconcile {--verify-no-op}', function (): int {
    $incident = Incident::query()->where('type', 'paid_but_locked')->firstOrFail();
    $user = User::query()->where('workspace_id', $incident->workspace_id)->first();
    $action = resolve(ReconciliationService::class)->reconcile($incident, $user);
    $this->info("Reconciliation result: {$action->result}; business effects: {$action->business_effects}.");

    if ($this->option('verify-no-op')) {
        $second = resolve(ReconciliationService::class)->reconcile($incident->fresh(), $user);
        $this->info("Repeat result: {$second->result}; business effects: {$second->business_effects}.");
    }

    return self::SUCCESS;
})->purpose('Safely reconcile the paid-but-locked incident and optionally verify a repeat no-op');
