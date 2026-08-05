<?php

namespace App\Services;

use App\Jobs\ProvisionEntitlement;
use App\Models\Account;
use App\Models\Entitlement;
use App\Models\LabRun;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\WebhookDelivery;
use App\Models\WebhookEvent;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WebhookIngestor
{
    public function __construct(private readonly WebhookSignature $signatures) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array{delivery: WebhookDelivery, event: ?WebhookEvent, accepted: bool, duplicate: bool}
     */
    public function ingest(
        Workspace $workspace,
        array $payload,
        string $signature,
        string $deliveryId,
        ?LabRun $run = null,
        bool $dispatch = true,
    ): array {
        $provider = (string) config('rescue_lab.provider');
        $eventId = (string) ($payload['event_id'] ?? 'missing');
        $payloadHash = $this->signatures->hash($payload);
        $validSignature = $this->signatures->verify($payload, $signature);

        $existingDelivery = WebhookDelivery::query()
            ->where('workspace_id', $workspace->id)
            ->where('provider', $provider)
            ->where('provider_delivery_id', $deliveryId)
            ->first();

        if ($existingDelivery) {
            return [
                'delivery' => $existingDelivery,
                'event' => $existingDelivery->event,
                'accepted' => $existingDelivery->response_code === 202,
                'duplicate' => true,
            ];
        }

        if (! $validSignature) {
            $delivery = WebhookDelivery::query()->create([
                'workspace_id' => $workspace->id,
                'lab_run_id' => $run?->id,
                'provider' => $provider,
                'provider_event_id' => $eventId,
                'provider_delivery_id' => $deliveryId,
                'signature_valid' => false,
                'duplicate_event' => false,
                'payload_hash' => $payloadHash,
                'response_code' => 401,
                'received_at' => now(),
            ]);

            return ['delivery' => $delivery, 'event' => null, 'accepted' => false, 'duplicate' => false];
        }

        return DB::transaction(function () use ($workspace, $payload, $deliveryId, $run, $dispatch, $provider, $eventId, $payloadHash): array {
            $account = $this->account($workspace, (string) ($payload['account_id'] ?? ''));
            $subscription = $this->subscription($workspace, (string) ($payload['subscription_id'] ?? ''));
            $payment = $this->payment($workspace, (string) ($payload['payment_id'] ?? ''));

            $event = WebhookEvent::query()->firstOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'provider' => $provider,
                    'provider_event_id' => $eventId,
                ],
                [
                    'lab_run_id' => $run?->id,
                    'account_id' => $account->id,
                    'subscription_id' => $subscription->id,
                    'payment_id' => $payment->id,
                    'event_type' => (string) ($payload['type'] ?? 'unknown'),
                    'payload_hash' => $payloadHash,
                    'payload' => $payload,
                    'status' => 'received',
                    'received_at' => now(),
                ],
            );

            $duplicate = ! $event->wasRecentlyCreated;
            $delivery = WebhookDelivery::query()->create([
                'workspace_id' => $workspace->id,
                'lab_run_id' => $run?->id,
                'webhook_event_id' => $event->id,
                'provider' => $provider,
                'provider_event_id' => $eventId,
                'provider_delivery_id' => $deliveryId,
                'signature_valid' => true,
                'duplicate_event' => $duplicate,
                'payload_hash' => $payloadHash,
                'response_code' => 202,
                'received_at' => now(),
            ]);

            if (! $duplicate && $event->status === 'received') {
                $this->consume($event, $subscription, $payment, $run, $dispatch);
            }

            if ($run) {
                $run->update([
                    'webhook_deliveries' => WebhookDelivery::query()->where('lab_run_id', $run->id)->count(),
                    'business_actions' => WebhookEvent::query()->where('lab_run_id', $run->id)->sum('business_effects'),
                ]);
            }

            return ['delivery' => $delivery, 'event' => $event->fresh(), 'accepted' => true, 'duplicate' => $duplicate];
        });
    }

    private function consume(WebhookEvent $event, Subscription $subscription, Payment $payment, ?LabRun $run, bool $dispatch): void
    {
        $subscription = Subscription::query()->whereKey($subscription->id)->lockForUpdate()->firstOrFail();

        if ($event->event_type !== 'payment.succeeded' || $payment->status !== 'succeeded') {
            $event->update([
                'status' => 'rejected',
                'rejection_reason' => 'Event cannot advance a subscription without a successful payment.',
                'processed_at' => now(),
            ]);

            return;
        }

        if (! in_array($subscription->status, ['pending', 'active'], true)) {
            $event->update([
                'status' => 'rejected',
                'rejection_reason' => "Unsafe transition from {$subscription->status} was blocked.",
                'processed_at' => now(),
            ]);

            return;
        }

        if ($subscription->status === 'pending') {
            $subscription->update(['status' => 'active']);
        }

        $entitlement = Entitlement::query()->firstOrCreate(
            [
                'workspace_id' => $subscription->workspace_id,
                'subscription_id' => $subscription->id,
                'entitlement_key' => 'core-platform-access',
            ],
            [
                'lab_run_id' => $run?->id,
                'account_id' => $subscription->account_id,
                'payment_id' => $payment->id,
                'state' => 'locked',
                'requested_at' => now(),
            ],
        );

        $event->update(['status' => 'processed', 'business_effects' => 1, 'processed_at' => now()]);

        if ($dispatch && $entitlement->wasRecentlyCreated && $run) {
            ProvisionEntitlement::dispatch($entitlement->id, $run->id, $event->id);
        }
    }

    private function account(Workspace $workspace, string $externalId): Account
    {
        return Account::query()->whereBelongsTo($workspace)->where('external_id', $externalId)->firstOrFail();
    }

    private function subscription(Workspace $workspace, string $externalId): Subscription
    {
        return Subscription::query()->whereBelongsTo($workspace)->where('external_id', $externalId)->firstOrFail();
    }

    private function payment(Workspace $workspace, string $externalId): Payment
    {
        $payment = Payment::query()->where('workspace_id', $workspace->id)->where('external_id', $externalId)->first();

        if (! $payment) {
            throw ValidationException::withMessages(['payment_id' => 'Payment does not belong to this workspace.']);
        }

        return $payment;
    }
}
