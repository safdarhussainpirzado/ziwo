<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\CarriagewayController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\TtsScriptController;
use App\Http\Controllers\GeospatialMarkerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect(auth()->user()->getLandingPageRoute());
    }
    return redirect()->route('login');
});

// Health check for HAProxy
Route::get('/up', function () {
    return response()->noContent();
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('can:dashboard.view');
    Route::get('/auth/heartbeat', function() { return response()->noContent(); })->name('auth.heartbeat');
    
    Route::get('/calls/pending', [CallController::class, 'pending'])->name('calls.pending');
    Route::get('/calls/inprogress', [CallController::class, 'inprogress'])->name('calls.inprogress');
    Route::get('/calls/completed', [CallController::class, 'completed'])->name('calls.completed');
    Route::patch('/calls/{call}/status', [CallController::class, 'updateStatus'])->name('calls.updateStatus');
    Route::get('/calls/export', [CallController::class, 'exportCsv'])->name('calls.export');
    Route::resource('calls', CallController::class);

    // Advanced Operational AJAX
    Route::get('/api/lookup-caller', [CallController::class, 'searchCallerHistory'])->name('api.lookupCaller');
    Route::get('/api/beat-resources', [CallController::class, 'getBeatResources'])->name('api.beatResources');
    Route::get('/ajax/geospatial-lookup', [CallController::class, 'geospatialLookup'])->name('api.geospatialLookup');
    Route::get('/ajax/spatial-contacts', [CallController::class, 'spatialContacts'])->name('api.spatialContacts');
    Route::get('/api/notifications/poll', [CallController::class, 'getNotificationsPoll'])->name('api.notifications.poll');
    Route::post('/api/dispatch/ping', [CallController::class, 'sendReminder'])->name('api.calls.reminder');
    
    // Administrative Modules
    Route::prefix('mgmt')->name('admin.')->group(function() {
        Route::middleware('can:users.view')->group(function() {
            Route::resource('users', UserController::class);
            Route::post('users/{user}/toggle-status',  [UserController::class, 'toggleStatus'])->name('users.toggleStatus')->middleware('can:users.manage_status');
            Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword')->middleware('can:users.manage_password');

            // Dynamic multi-scope management
            Route::get('users/{user}/scopes',                    [UserController::class, 'getScopes'])->name('users.scopes.index')->middleware('can:users.manage_scopes');
            Route::post('users/{user}/scopes',                   [UserController::class, 'addScope'])->name('users.scopes.add')->middleware('can:users.manage_scopes');
            Route::patch('users/{user}/scopes/{scope}',          [UserController::class, 'updateScope'])->name('users.scopes.update')->middleware('can:users.manage_scopes');
            Route::delete('users/{user}/scopes/{scope}',         [UserController::class, 'removeScope'])->name('users.scopes.remove')->middleware('can:users.manage_scopes');
        });

        Route::middleware('can:roles.view')->group(function() {
            Route::resource('roles', RoleController::class);
            Route::post('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->name('roles.toggleStatus');
            Route::resource('permissions', \App\Http\Controllers\PermissionController::class)->middleware('can:permissions.view');
            Route::post('permissions/{permission}/toggle-status', [\App\Http\Controllers\PermissionController::class, 'toggleStatus'])->name('permissions.toggleStatus');
            Route::get('audit', [AuditController::class, 'index'])->name('audit.index')->middleware('can:system.audit_view');
            Route::resource('tts-scripts', TtsScriptController::class)->except(['show', 'edit', 'update'])->middleware('can:system.tts_scripts.manage');
        });

        Route::middleware('can:system.settings.view')->group(function() {
            Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
            Route::patch('settings', [SettingController::class, 'update'])->name('settings.update')->middleware('can:system.settings.update');
        });

        Route::middleware('can:geography.offices.view')->group(function() {
            Route::get('offices', [OfficeController::class, 'index'])->name('offices.index');
            
            Route::post('offices', [OfficeController::class, 'store'])->name('offices.store')->middleware('can:geography.offices.create');
            Route::patch('offices/{office}', [OfficeController::class, 'update'])->name('offices.update')->middleware('can:geography.offices.update');
            Route::delete('offices/{office}', [OfficeController::class, 'destroy'])->name('offices.destroy')->middleware('can:geography.offices.delete');
            Route::post('offices/{office}/toggle-status', [OfficeController::class, 'toggleStatus'])->name('offices.toggleStatus')->middleware('can:geography.offices.update');
            
            Route::resource('carriageways', CarriagewayController::class);
            Route::post('carriageways/{carriageway}/toggle-status', [CarriagewayController::class, 'toggleStatus'])->name('carriageways.toggleStatus')->middleware('can:geography.carriageways.manage');

            Route::resource('geospatial-markers', GeospatialMarkerController::class)->middleware('can:geography.geospatial.view');
            Route::post('geospatial-markers/{geospatialMarker}/toggle-status', [GeospatialMarkerController::class, 'toggleStatus'])->name('admin.geospatial-markers.toggleStatus')->middleware('can:geography.geospatial.manage');
        });
    });
    
    
    // Reports Module
    Route::prefix('reports')->name('reports.')->group(function() {
        Route::get('/call-type-summary', [ReportController::class, 'callTypeSummary'])->name('call-type-summary')->middleware('can:reports.call_type_summary');
        Route::get('/beat-wise', [ReportController::class, 'beatWise'])->name('beat-wise')->middleware('can:reports.beat_wise');
        Route::get('/agent-wise', [ReportController::class, 'agentWise'])->name('agent-wise')->middleware('can:reports.agent_wise');
        Route::get('/sla-compliance', [ReportController::class, 'slaCompliance'])->name('sla-compliance')->middleware('can:reports.sla_compliance');
        Route::get('/max-response-time', [ReportController::class, 'maxResponseTime'])->name('max-response-time')->middleware('can:reports.max_response_time');
        Route::get('/predictive-analysis', [ReportController::class, 'predictiveAnalysis'])->name('predictive-analysis')->middleware('can:reports.predictive_analysis');
        Route::get('/category-analysis', [ReportController::class, 'categoryAnalysis'])->name('category-analysis')->middleware('can:reports.view');
        Route::get('/junk-calls-frequency', [ReportController::class, 'junkCallsFrequency'])->name('junk-calls-frequency')->middleware('can:reports.view');
    });

    // Telephony Softphone Actions
    Route::prefix('telephony')->name('telephony.')->group(function() {
        Route::post('/authenticate', [\App\Http\Controllers\TelephonyController::class, 'authenticate'])->name('authenticate');
        Route::get('/status', [\App\Http\Controllers\TelephonyController::class, 'getStatus'])->name('status');
        Route::get('/calls/recent', [\App\Http\Controllers\TelephonyController::class, 'recentCalls'])->name('calls.recent');
        Route::post('/disconnect', [\App\Http\Controllers\TelephonyController::class, 'disconnect'])->name('disconnect');
        Route::post('/answer', [\App\Http\Controllers\TelephonyController::class, 'answer'])->name('answer');
        Route::post('/dial', [\App\Http\Controllers\TelephonyController::class, 'dial'])->name('dial');
        Route::post('/hold', [\App\Http\Controllers\TelephonyController::class, 'hold'])->name('hold');
        Route::post('/resume', [\App\Http\Controllers\TelephonyController::class, 'resume'])->name('resume');
        Route::post('/mute', [\App\Http\Controllers\TelephonyController::class, 'mute'])->name('mute');
        Route::post('/unmute', [\App\Http\Controllers\TelephonyController::class, 'unmute'])->name('unmute');
        Route::post('/hangup', [\App\Http\Controllers\TelephonyController::class, 'hangup'])->name('hangup');
        Route::post('/transfer', [\App\Http\Controllers\TelephonyController::class, 'transfer'])->name('transfer');
        Route::post('/conference', [\App\Http\Controllers\TelephonyController::class, 'conference'])->name('conference');
        Route::post('/recording', [\App\Http\Controllers\TelephonyController::class, 'toggleRecording'])->name('recording');
        Route::get('/queues', [\App\Http\Controllers\TelephonyController::class, 'getQueues'])->name('queues');
        Route::get('/teammates', [\App\Http\Controllers\TelephonyController::class, 'getTeammates'])->name('teammates');
        
        // Centralized Phonebook
        Route::get('/phonebook', [\App\Http\Controllers\TelephonyController::class, 'searchPhonebook'])->name('phonebook.search');
        Route::post('/phonebook', [\App\Http\Controllers\TelephonyController::class, 'storePhonebook'])->name('phonebook.store');
        Route::delete('/phonebook/{id}', [\App\Http\Controllers\TelephonyController::class, 'destroyPhonebook'])->name('phonebook.destroy');
    });

    // Telephony Admin Dashboard
    Route::middleware('can:dashboard.view')->group(function() {
        Route::get('mgmt/telephony', [\App\Http\Controllers\TelephonyAdminController::class, 'index'])->name('admin.telephony.index');
        Route::get('mgmt/telephony/live-stats', [\App\Http\Controllers\TelephonyAdminController::class, 'getLiveStats'])->name('admin.telephony.live-stats');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
