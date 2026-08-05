<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->string('name');
            $table->string('segment');
            $table->string('owner_name')->nullable();
            $table->timestamps();
            $table->unique(['workspace_id', 'external_id']);
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->string('plan');
            $table->string('status')->index();
            $table->decimal('expected_monthly_amount', 12, 2);
            $table->char('currency', 3)->default('USD');
            $table->date('started_at')->nullable();
            $table->date('canceled_at')->nullable();
            $table->timestamps();
            $table->unique(['workspace_id', 'external_id']);
            $table->index(['workspace_id', 'status']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('USD');
            $table->string('status')->index();
            $table->date('due_date');
            $table->timestamps();
            $table->unique(['workspace_id', 'external_id']);
            $table->index(['workspace_id', 'period_start']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->decimal('amount', 12, 2);
            $table->string('status')->index();
            $table->timestamp('attempted_at');
            $table->string('failure_code')->nullable();
            $table->timestamps();
            $table->unique(['workspace_id', 'external_id']);
        });

        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('fingerprint', 191);
            $table->string('type')->index();
            $table->string('severity')->index();
            $table->string('status')->default('open')->index();
            $table->string('title');
            $table->text('reason');
            $table->text('recommended_action');
            $table->decimal('revenue_impact', 12, 2);
            $table->char('currency', 3)->default('USD');
            $table->string('assignee')->nullable();
            $table->json('evidence')->nullable();
            $table->timestamp('detected_at');
            $table->timestamp('last_detected_at');
            $table->timestamp('recovered_at')->nullable();
            $table->timestamps();
            $table->unique(['workspace_id', 'fingerprint']);
            $table->index(['workspace_id', 'status', 'severity']);
        });

        Schema::create('incident_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('author_name');
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('audit_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('initiated_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->string('status');
            $table->unsignedInteger('records_scanned')->default(0);
            $table->unsignedInteger('findings_count')->default(0);
            $table->unsignedInteger('new_incidents_count')->default(0);
            $table->unsignedInteger('duration_ms')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['workspace_id', 'completed_at']);
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('message');
            $table->json('context')->nullable();
            $table->timestamps();
            $table->index(['workspace_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
        Schema::dropIfExists('audit_runs');
        Schema::dropIfExists('incident_notes');
        Schema::dropIfExists('incidents');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('accounts');
    }
};
