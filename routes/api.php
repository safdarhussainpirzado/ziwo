<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CallController;
use App\Models\Carriageway;

/*
|--------------------------------------------------------------------------
| API Routes — ALL routes require authentication (C-4 fix)
|--------------------------------------------------------------------------
| Rate limiting applied both here AND at Nginx level (defense-in-depth).
*/

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    Route::get('/user', fn(Request $request) => $request->user());

    // Beat resource lookup (tigers, officers) — served by CallController
    Route::get('/beat-resources', [CallController::class, 'getBeatResources']);

    // Unified search — tighter rate limit (geospatial OSINT reduction)
    Route::middleware('throttle:30,1')
        ->get('/unified-search', function (Request $request) {
            $q = $request->string('q')->trim();
            if ($q->length() < 2) {
                return response()->json(['results' => []]);
            }

            $carriageways = Carriageway::where(function ($query) use ($q) {
                $query->where('road_name', 'like', "%{$q}%")
                      ->orWhere('road_short', 'like', "%{$q}%")
                      ->orWhere('road', 'like', "%{$q}%");
            })->limit(5)->get(['id', 'road_name', 'road_short', 'type']);

            $beats = \App\Models\Office::beats()->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%");
            })->with(['parent.parent'])
              ->limit(5)
              ->get(['id', 'name', 'parent_id', 'type']);

            $formattedBeats = $beats->map(function($b) {
                return [
                    'id' => $b->id,
                    'name' => $b->name,
                    'sector_name' => $b->parent?->name,
                    'zone_name' => $b->parent?->parent?->name,
                ];
            });

            return response()->json([
                'carriageways' => $carriageways,
                'beats'        => $formattedBeats,
            ]);
        })->name('api.unified-search');
});

// Public Webhook endpoint for ZIWO events (protected via signature token validation)
Route::post('/telephony/webhook', [\App\Http\Controllers\TelephonyWebhookController::class, 'handle'])->name('api.telephony.webhook');