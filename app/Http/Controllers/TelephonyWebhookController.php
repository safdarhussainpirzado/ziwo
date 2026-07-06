<?php

namespace App\Http\Controllers;

use App\Telephony\Jobs\ProcessWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelephonyWebhookController extends Controller
{
    /**
     * Endpoint for ZIWO Telephony webhooks.
     */
    public function handle(Request $request)
    {
        // 1. Webhook Signature/Token Validation (Defense-in-depth security)
        $configuredSecret = env('TELEPHONY_WEBHOOK_SECRET');
        if ($configuredSecret) {
            $providedSecret = $request->header('X-Telephony-Webhook-Token') ?? $request->query('token');
            if ($providedSecret !== $configuredSecret) {
                Log::warning('Unauthorized telephony webhook event rejected. Invalid secret signature.');
                return response()->json(['status' => 'error', 'message' => 'Unauthorized signature'], 401);
            }
        }

        // 2. Parse payload details
        $eventType = $request->input('event');
        $payload = $request->all();

        if (empty($eventType)) {
            return response()->json(['status' => 'error', 'message' => 'Missing event type parameter'], 400);
        }

        // 3. Dispatch to Redis Queue for async processing
        ProcessWebhookJob::dispatch($eventType, $payload);

        return response()->json([
            'status' => 'queued',
            'message' => 'Telephony event queued for processing'
        ]);
    }
}
