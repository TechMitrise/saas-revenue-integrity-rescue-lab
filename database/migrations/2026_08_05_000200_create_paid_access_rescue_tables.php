<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->decimal('monthly_price', 12, 2);
            $table->char('currency', 3)->default('USD');
            $table->timestamps();
            $table->unique(['workspace_id', 'code']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('account_id')->constrained()->nullOnDelete();
            $table->unsignedInteger('access_sla_seconds')->default(120)->after('currency');
        });

        Schema::create('lab_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('scenario_key');
            $table->string('mode')->index();
            $table->uuid('correlation_id');
            $table->string('status')->default('running');
            $table->unsignedInteger('webhook_deliveries')->default(0);
            $table->unsignedInteger('business_actions')->default(0);
            $table->unsignedInteger('queue_attempts')->default(0);
            $table->unsignedInteger('queue_failures')->default(0);
            $table->string('entitlement_state')->default('locked');
            $table->unsignedBigInteger('payment_to_access_ms')->nullable();
            $table->unsignedBigInteger('sla_threshold_ms');
            $table->boolean('sla_breached')->default(false);
            $table->string('reconciliation_result')->nullable();
            $table->json('before_state')->nullable();
            $table->json('after_state')->nullable();
            $table->unsignedInteger('query_count')->default(0);
            $table->unsignedInteger('processing_ms')->default(0);
            $table->json('evidence')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['workspace_id', 'mode']);
            $table->unique(['workspace_id', 'correlation_id']);
        });

        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lab_run_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('provider_event_id');
            $table->string('event_type');
            $table->string('payload_hash', 64);
            $table->json('payload');
            $table->string('status')->default('received');
            $table->unsignedInteger('business_effects')->default(0);
            $table->string('rejection_reason')->nullable();
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['workspace_id', 'provider', 'provider_event_id'], 'webhook_events_provider_unique');
        });

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lab_run_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('webhook_event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider');
            $table->string('provider_event_id');
            $table->string('provider_delivery_id');
            $table->boolean('signature_valid');
            $table->boolean('duplicate_event')->default(false);
            $table->string('payload_hash', 64);
            $table->unsignedSmallInteger('response_code');
            $table->timestamp('received_at');
            $table->timestamps();
            $table->unique(['workspace_id', 'provider', 'provider_delivery_id'], 'webhook_deliveries_provider_unique');
        });

        Schema::create('entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lab_run_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('entitlement_key');
            $table->string('state')->default('locked')->index();
            $table->timestamp('requested_at');
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamps();
            $table->unique(['workspace_id', 'subscription_id', 'entitlement_key'], 'entitlements_business_unique');
        });

        Schema::create('provisioning_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lab_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entitlement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('webhook_event_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('job_uuid');
            $table->unsignedSmallInteger('attempt_number');
            $table->string('status');
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();
            $table->unsignedInteger('duration_ms');
            $table->timestamp('started_at');
            $table->timestamp('completed_at');
            $table->timestamps();
            $table->unique(['entitlement_id', 'attempt_number']);
            $table->index(['workspace_id', 'status']);
        });

        Schema::table('incidents', function (Blueprint $table) {
            $table->foreignId('lab_run_id')->nullable()->after('workspace_id')->constrained()->nullOnDelete();
            $table->string('root_cause')->nullable()->after('reason');
            $table->timestamp('sla_breached_at')->nullable()->after('last_detected_at');
        });

        Schema::create('reconciliation_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lab_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entitlement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->uuid('action_id');
            $table->string('result');
            $table->unsignedSmallInteger('business_effects')->default(0);
            $table->json('before_state');
            $table->json('after_state');
            $table->string('reason');
            $table->timestamp('created_at');
            $table->unique(['workspace_id', 'action_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reconciliation_actions');
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lab_run_id');
            $table->dropColumn(['root_cause', 'sla_breached_at']);
        });
        Schema::dropIfExists('provisioning_attempts');
        Schema::dropIfExists('entitlements');
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('lab_runs');
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
            $table->dropColumn('access_sla_seconds');
        });
        Schema::dropIfExists('plans');
    }
};
