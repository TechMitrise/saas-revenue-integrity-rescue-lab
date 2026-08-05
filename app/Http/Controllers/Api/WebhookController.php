<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LabRun;
use App\Models\Workspace;
use App\Services\WebhookIngestor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function store(Request $request, WebhookIngestor $ingestor): JsonResponse
    {
        $payload = $request->validate([
            'workspace' => ['required', 'string'],
            'event_id' => ['required', 'string', 'max:191'],
            'type' => ['required', 'string', 'max:100'],
            'account_id' => ['required', 'string'],
            'subscription_id' => ['required', 'string'],
            'payment_id' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'currency' => ['required', 'string', 'size:3'],
            'synthetic' => ['required', 'accepted'],
        ]);
        $workspace = Workspace::query()->where('slug', $payload['workspace'])->firstOrFail();
        $run = LabRun::query()
            ->whereBelongsTo($workspace)
            ->where('mode', $request->header('X-Rescue-Lab-Mode', 'stabilised'))
            ->first();
        $result = $ingestor->ingest(
            $workspace,
            $payload,
            (string) $request->header('X-Synthetic-Signature'),
            (string) $request->header('X-Synthetic-Delivery-Id'),
            $run,
        );

        return response()->json([
            'accepted' => $result['accepted'],
            'duplicate' => $result['duplicate'],
            'providerEventId' => $result['event']?->provider_event_id,
        ], $result['accepted'] ? 202 : 401);
    }
}
