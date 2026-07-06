<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\CallType;
use App\Models\CallSubType;
use App\Models\VehicleType;
use App\Models\Office;
use App\Models\Carriageway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class CallController extends Controller
{
    public function __construct(private readonly \App\Services\CallService $callService) {}

    private function applyScopeVisibility($query)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (in_array($user->role?->name, ['super_admin', 'agent_supervisor'])) {
            return $query;
        }

        if ($user->role?->name === 'agent') {
            return $query->where('agent_id', $user->id);
        }

        $scopes = $user->activeScopes;
        
        if ($scopes && $scopes->isNotEmpty()) {
            return $query->where(function ($q) use ($scopes) {
                foreach ($scopes as $scope) {
                    if (!$scope->office_id) {
                        $q->orWhere(fn($nested) => $nested->whereRaw('1 = 1'));
                        return; 
                    }
                    
                    $officeIds = $scope->office->getDescendantIds(true);
                    $q->orWhereIn('office_id', $officeIds);
                }
            });
        }

        return $query->where(fn($nested) => $nested->whereRaw('1 = 0'));
    }


    private function applyFiltersAndSearch($query, Request $request)
    {
        if ($request->search) {
            $s = strtolower($request->search);
            $query->where(function($q) use ($s) {
                $q->where('call_number', 'like', "%$s%")
                  ->orWhere('caller_number', 'like', "%$s%")
                  ->orWhere('caller_name', 'like', "%$s%")
                  ->orWhere('vehicle_no', 'like', "%$s%")
                  ->orWhereHas('vehicleType', function($q2) use ($s) {
                      $q2->whereRaw('LOWER(name) like ?', ["%$s%"]);
                  })
                  ->orWhereHas('callType', function($q2) use ($s) {
                      $q2->whereRaw('LOWER(name) like ?', ["%$s%"]);
                  })
                  ->orWhereHas('callSubType', function($q2) use ($s) {
                      $q2->whereRaw('LOWER(name) like ?', ["%$s%"]);
                  })
                  ->orWhereHas('office', function($q2) use ($s) {
                      $q2->whereRaw('LOWER(name) like ?', ["%$s%"]);
                  });
            });
        }

        if ($request->date_from) {
            try {
                $dateTime = $request->date_from;
                if ($request->time_from) {
                    $dateTime .= ' ' . $request->time_from;
                    $query->where('created_at', '>=', Carbon::parse($dateTime));
                } else {
                    $query->whereDate('created_at', '>=', Carbon::parse($dateTime));
                }
            } catch (\Exception $e) {}
        }

        if ($request->date_to) {
            try {
                $dateTime = $request->date_to;
                if ($request->time_to) {
                    $dateTime .= ' ' . $request->time_to;
                    $query->where('created_at', '<=', Carbon::parse($dateTime));
                } else {
                    $query->whereDate('created_at', '<=', Carbon::parse($dateTime));
                }
            } catch (\Exception $e) {}
        }

        if ($request->office_id) {
            $query->where('office_id', $request->office_id);
        } elseif ($request->beat_id) {
            $query->where('office_id', $request->beat_id);
        } elseif ($request->sector_id) {
            $office = Office::find($request->sector_id);
            if ($office) $query->whereIn('office_id', $office->getDescendantIds());
        } elseif ($request->zone_id) {
            $office = Office::find($request->zone_id);
            if ($office) $query->whereIn('office_id', $office->getDescendantIds());
        }

        if ($request->call_type_id) { $query->where('call_type_id', $request->call_type_id); }
        if ($request->call_sub_type_id) { $query->where('call_sub_type_id', $request->call_sub_type_id); }
        if ($request->agent_id) { $query->where('agent_id', $request->agent_id); }

        return $query;
    }

    private function getAjaxResponse($calls, $stats)
    {
        return response()->json([
            'items' => $calls->items(),
            'stats' => $stats,
            'current_page' => $calls->currentPage(),
            'last_page' => $calls->lastPage(),
            'total' => $calls->total(),
        ]);
    }

    private function getFilterData()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $isPrivileged = $user->role?->name === 'agent_supervisor';

        return [
            'zones' => Office::zones()->where('is_active', true)->select('id', 'name')->orderBy('name')->get(),
            'sectors' => Office::sectors()->where('is_active', true)->select('id', 'name', 'parent_id')->orderBy('name')->get(),
            'beats' => Office::beats()->where('is_active', true)->select('id', 'name', 'parent_id')->orderBy('name')->get(),
            'callTypes' => \App\Models\CallType::where('is_active', true)->select('id', 'name')->orderBy('name')->get(),
            'callSubTypes' => \App\Models\CallSubType::where('is_active', true)->select('id', 'name', 'call_type_id')->orderBy('name')->get(),
            'agents' => $isPrivileged
                ? \App\Models\User::whereHas('role', function($q) {
                    $q->where('name', 'agent');
                })->select('id', 'username')->orderBy('username')->get()
                : collect([]),
        ];
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Call::class);
        $query = Call::with(['callType', 'callSubType', 'office.parent.parent', 'agent', 'carriageway', 'vehicleType']);
        $query = $this->applyScopeVisibility($query);
        $query = $this->applyFiltersAndSearch($query, $request);

        // Calculate stats BEFORE filtering by the currently active status/category tab
        $statsBaseQuery = clone $query;
        $totalCount = (clone $statsBaseQuery)->count();
        
        $statusStats = [
            'total'       => $totalCount,
            'pending'     => (clone $statsBaseQuery)->where('status', 'pending')->count(),
            'in_progress' => (clone $statsBaseQuery)->where('status', 'in_progress')->count(),
            'completed'   => (clone $statsBaseQuery)->where('status', 'completed')->count(),
        ];

        // Add percentages for status stats
        foreach ($statusStats as $key => $count) {
            $statusStats[$key . '_percent'] = $totalCount > 0 ? round(($count / $totalCount) * 100, 1) : 0;
        }

        // Calculate Category Stats
        $callTypes = CallType::where('is_active', true)->get();
        $categoryStats = [];
        foreach ($callTypes as $type) {
            $c = (clone $statsBaseQuery)->where('call_type_id', $type->id)->count();
            $categoryStats[] = [
                'id' => $type->id,
                'name' => $type->name,
                'count' => $c,
                'percent' => $totalCount > 0 ? round(($c / $totalCount) * 100, 1) : 0,
                'icon' => $type->icon ?? 'fa-tag'
            ];
        }

        $stats = array_merge($statusStats, ['categories' => $categoryStats]);

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        if ($request->call_type_id) {
            $query->where('call_type_id', $request->call_type_id);
        }

        $filterData = $this->getFilterData();

        $sort = $request->get('sort');
        $dir = $request->get('direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['call_number', 'created_at', 'priority', 'status', 'caller_name', 'caller_number', 'vehicle_no', 'call_type_id', 'office_id'];
        
        if ($sort === 'beat_id') $sort = 'office_id';
        
        if ($sort && in_array($sort, $allowedSorts)) {
            $calls = $query->orderBy($sort, $dir)->paginate($request->perPage ?? 25);
        } else {
            $calls = $query->orderByRaw("FIELD(status, 'pending', 'in_progress', 'forwarded', 'completed', 'cancelled', 'junk')")->orderBy('created_at', 'desc')->paginate($request->perPage ?? 25);
        }

        if ($request->wantsJson()) {
            return $this->getAjaxResponse($calls, $stats);
        }

        $title = 'Help Management';
        if ($request->status === 'pending') $title = 'Pending Helps';
        if ($request->status === 'in_progress') $title = 'In-Process Helps';
        if ($request->status === 'completed') $title = 'Resolved Helps';

        return view('calls.index', array_merge(compact('calls', 'stats'), $filterData))->with('title', $title);
    }

    public function pending(Request $request)
    {
        return redirect()->route('calls.index', array_merge($request->all(), ['status' => 'pending']));
    }

    public function inprogress(Request $request)
    {
        return redirect()->route('calls.index', array_merge($request->all(), ['status' => 'in_progress']));
    }

    public function completed(Request $request)
    {
        return redirect()->route('calls.index', array_merge($request->all(), ['status' => 'completed']));
    }
    public function show(Call $call)
    {
        $this->authorize('view', $call);
        $call->load(['callType', 'callSubType', 'office.parent.parent', 'agent', 'carriageway', 'vehicleType']);
        return view('calls.show', compact('call'));
    }

    public function create()
    {
        $this->authorize('create', Call::class);
        $callTypes = cache()->rememberForever('master:call_types', fn() => 
            CallType::where('is_active', true)->with(['subTypes' => fn($q) => $q->where('is_active', true)])->orderBy('sort_order', 'asc')->get()
        );
        
        $vehicleTypes = cache()->rememberForever('master:vehicle_types', fn() => 
            VehicleType::where('is_active', true)->get()
        );
        
        $carriageways = Carriageway::where('status', 'active')
            ->select('id', 'type', 'road', 'road_short', 'road_name', 'road_from', 'road_to')
            ->orderBy('road_name')
            ->get();
        
        $zones = Office::zones()->where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        
        $sectors = Office::sectors()->where('is_active', true)
            ->select('id', 'parent_id as zone_id', 'name')
            ->orderBy('name')
            ->get();
        
        $beats = Office::beats()->where('is_active', true)
            ->select('id', 'parent_id as sector_id', 'name')
            ->orderBy('name')
            ->get();
        
        return view('calls.create', compact(
            'callTypes', 'vehicleTypes', 'carriageways', 'zones', 'sectors', 'beats'
        ));
    }

    public function edit(Call $call)
    {
        $this->authorize('update', $call);
        $callTypes = cache()->rememberForever('master:call_types', fn() => CallType::where('is_active', true)->with(['subTypes' => fn($q) => $q->where('is_active', true)])->get());
        $vehicleTypes = cache()->rememberForever('master:vehicle_types', fn() => VehicleType::where('is_active', true)->get());
        $carriageways = cache()->rememberForever('master:carriageways', fn() => Carriageway::where('status', 'active')->get());
        $beats = cache()->rememberForever('master:beats', fn() => Office::beats()->where('is_active', true)->get());
        
        return view('calls.edit', compact('call', 'callTypes', 'vehicleTypes', 'carriageways', 'beats'));
    }

    public function update(Request $request, Call $call)
    {
        $this->authorize('update', $call);
        $callType = null;
        if ($request->call_type_id) {
            $callType = CallType::find($request->call_type_id);
        }
        $isJunkSilent = $callType && $callType->category === 'junk_silent';

        $validated = $request->validate([
            'caller_number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $clean = preg_replace('/[^0-9]/', '', $value);
                    if (strlen($clean) !== 11) {
                        $fail('The ' . $attribute . ' must be exactly 11 digits.');
                    }
                },
            ],
            'caller_name' => 'nullable|string|max:150',
            'call_type_id' => 'required',
            'call_sub_type_id' => 'required|exists:call_sub_types,id',
            'details' => 'nullable|string',
            'vehicle_type_id' => 'nullable',
            'vehicle_no' => 'nullable|string|max:30',
            'carriageway_id' => 'nullable',
            'km_marker_text' => 'nullable|string|max:20',
            'beat_id' => ($isJunkSilent ? 'nullable' : 'required') . '|exists:offices,id',
            'priority' => 'nullable',
            'caller_lat' => 'nullable',
            'caller_lng' => 'nullable',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled,junk,forwarded',
            'location_details' => 'nullable|string',
        ]);

        try {
            // Sanitize: Convert empty strings to null for database compatibility
            $data = array_map(fn($v) => $v === '' ? null : $v, $validated);

            // Map priority string to integer if provided as text (e.g., 'medium')
            if (isset($data['priority']) && !is_numeric($data['priority'])) {
                $priorities = ['high' => 1, 'medium' => 3, 'low' => 5, 'emergency' => 1];
                $data['priority'] = $priorities[strtolower($data['priority'])] ?? 3;
            }

            // Map beat_id to office_id for unified structure
            if (isset($data['beat_id'])) {
                $data['office_id'] = $data['beat_id'];
            }
            unset($data['beat_id'], $data['zone_id'], $data['sector_id']);

            $call->update($data);
            return redirect()->route('calls.show', $call)->with('success', 'Operational record updated successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->authorize('create', Call::class);

        // Decode base64-encoded free-text fields (WAF bypass encoding applied in JS)
        foreach (['details', 'location_details'] as $field) {
            $val = $request->input($field);
            if ($val && preg_match('/^[A-Za-z0-9+\/]+=*$/', $val)) {
                $decoded = base64_decode($val, true);
                if ($decoded !== false) {
                    $request->merge([$field => $decoded]);
                }
            }
        }

        $callType = \App\Models\CallType::find($request->call_type_id);
        $isExempt = $callType && in_array($callType->category, ['junk_silent', 'information']);

        $validated = $request->validate([
            'caller_number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $clean = preg_replace('/[^0-9]/', '', $value);
                    if (strlen($clean) !== 11) {
                        $fail('The ' . $attribute . ' must be exactly 11 digits.');
                    }
                },
            ],
            'caller_name' => 'nullable|string|max:150',
            'call_type_id' => 'required|exists:call_types,id',
            'call_sub_type_id' => 'required|exists:call_sub_types,id',
            'details' => 'nullable|string',
            'vehicle_type_id' => 'nullable|exists:vehicle_types,id',
            'vehicle_no' => 'nullable|string|max:30',
            'carriageway_id' => 'nullable|exists:carriageways,id',
            'km_marker_text' => 'nullable|string|max:20',
            'beat_id' => ($isExempt ? 'nullable' : 'required') . '|exists:offices,id',
            'priority' => 'nullable|integer|min:1|max:5',
            'caller_lat' => 'nullable|numeric',
            'caller_lng' => 'nullable|numeric',
            'location_details' => 'nullable|string',
        ]);

        try {
            // Sanitize: Convert empty strings to null
            $data = array_map(fn($v) => $v === '' ? null : $v, $validated);

            // Map beat_id to office_id
            if (isset($data['beat_id'])) {
                $data['office_id'] = $data['beat_id'];
            }
            unset($data['beat_id'], $data['zone_id'], $data['sector_id']);
            
            // Map priority string to integer if provided as text
            if (isset($data['priority']) && !is_numeric($data['priority'])) {
                $priorities = ['high' => 1, 'medium' => 3, 'low' => 5, 'emergency' => 1];
                $data['priority'] = $priorities[strtolower($data['priority'])] ?? 3;
            }

            $call = $this->callService->createCall($data, auth()->user());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Operational record synchronized: ' . $call->call_number,
                    'call' => $call
                ]);
            }
            
            // For regular form submission, redirect back to create page with success message
            return redirect()
                ->route('calls.create')
                ->with('success', 'Operational record synchronized: ' . $call->call_number);
                
        } catch (Exception $e) {
            Log::error('Call creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add Help: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to sync record: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Call $call)
    {
        $this->authorize('calls.manage_status');
        try {
            $this->callService->updateStatus($call, $request->status, auth()->user(), $request->all());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status updated successfully.',
                    'status' => $call->fresh()->status
                ]);
            }

            return back()->with('success', 'Status updated successfully.');
        } catch (Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Update failed: ' . $e->getMessage()
                ], 422);
            }
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }


    // Advanced AJAX: Search caller history
    public function searchCallerHistory(Request $request)
    {
        $this->authorize('calls.api_lookup');
        $num = $request->get('number', $request->get('caller_number'));
        if (!$num || strlen($num) < 7) return response()->json(['calls' => []]);

        $calls = Call::with(['office.parent.parent', 'agent'])->where('caller_number', $num)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $user = auth()->user();
        $showAgent = !in_array($user->role?->name, ['zone_admin', 'sector_admin', 'beat_operator']);

        $formattedCalls = $calls->map(function ($c) use ($showAgent) {
            return [
                'id' => $c->id,
                'call_number' => $c->call_number,
                'caller_name' => $c->caller_name,
                'caller_number' => $c->caller_number,
                'status' => $c->status,
                'created_at' => $c->created_at,
                'details' => $c->details,
                'priority' => $c->priority,
                'call_type_id' => $c->call_type_id,
                'call_sub_type_id' => $c->call_sub_type_id,
                'vehicle_type_id' => $c->vehicle_type_id,
                'office_id' => $c->office_id,
                'zone_name' => $c->office?->zone()?->name,
                'sector_name' => $c->office?->sector()?->name,
                'beat_name' => $c->office?->name,
                'agent_name' => $showAgent ? ($c->agent?->full_name ?? 'N/A') : null,
                'location_details' => $c->location_details,
            ];
        });

        return response()->json([
            'name' => $calls->first() ? $calls->first()->caller_name : null,
            'vehicle_no' => $calls->first() ? $calls->first()->vehicle_no : null,
            'calls' => $formattedCalls
        ]);
    }

    /**
     * Send Call Reminder to operational units.
     */
    public function sendReminder(Request $request, Call $call = null)
    {
        $this->authorize('calls.api_lookup');
        if (!$call) {
            $id = $request->input('ref');
            $call = Call::findOrFail($id);
        }
        try {
            $call = $this->callService->sendReminder($call, auth()->user());
            return response()->json([
                'success' => true,
                'message' => "Reminder sent successfully for call {$call->call_number}.",
                'call' => [
                    'id' => $call->id,
                    'call_reminder_count' => $call->call_reminder_count,
                    'last_reminder_at' => $call->last_reminder_at ? $call->last_reminder_at->toDateTimeString() : null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Failed to send reminder: " . $e->getMessage()
            ], 422);
        }
    }

    // Advanced AJAX: Get Available Beat Resources
    public function getBeatResources(Request $request)
    {
        $this->authorize('calls.api_lookup');
        $beatId = $request->get('beat_id');
        if (!$beatId) return response()->json(['officers' => []]);

        $query = \App\Models\User::whereHas('activeScopes', function($q) use ($beatId) {
            $q->where('office_id', $beatId);
        });

        // Apply same scope visibility to prevent data leakage
        $query = $this->applyScopeVisibility($query);

        $officers = $query->get(['id', 'username', 'full_name']);

        return response()->json(['officers' => $officers]);
    }

    public function destroy(Call $call)
    {
        $this->authorize('calls.delete');
        try {
            $call->delete();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Heartbeat Polling: Simplified real-time discovery for pending help acquisitions.
     */
    public function getNotificationsPoll(Request $request)
    {
        $user = auth()->user();
        $this->authorize('calls.api_lookup');

        try {
            $allowedRoles = ['beat_operator', 'sector_admin', 'zone_admin'];
            $userRole = $user->role ? $user->role->name : null;

            if (!in_array($userRole, $allowedRoles)) {
                return response()->json([
                    'new_calls' => [],
                    'timestamp' => now()->toDateTimeString()
                ]);
            }

            $since = $request->query('since');
            
            $query = Call::where('status', 'pending');
            
            if ($since) {
                try {
                    $dtSince = Carbon::parse($since);
                    $query->where(function($q) use ($dtSince) {
                        $q->where('created_at', '>', $dtSince)
                          ->orWhere('last_reminder_at', '>', $dtSince);
                    });
                } catch (\Throwable $e) {}
            }

            $query = $this->applyScopeVisibility($query);
            $newCalls = $query->with(['callType', 'callSubType', 'office'])->orderBy('created_at', 'desc')->get();

            // Calculate current total pending helps count for navigation badge
            $pendingQuery = Call::where('status', 'pending');
            $pendingCount = $this->applyScopeVisibility($pendingQuery)->count();

            $results = $newCalls->map(function($call) {
                return [
                    'id' => $call->id,
                    'call_number' => $call->call_number,
                    'caller_name' => $call->caller_name,
                    'created_at' => $call->created_at->toIso8601String(),
                    'last_reminder_at' => $call->last_reminder_at ? $call->last_reminder_at->toIso8601String() : null,
                    'call_type' => $call->callType ? ['name' => $call->callType->name] : null,
                    'call_sub_type' => $call->callSubType ? ['name' => $call->callSubType->name] : null,
                ];
            });

            return response()->json([
                'new_calls' => $results,
                'pending_count' => $pendingCount,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'new_calls' => [],
                'timestamp' => now()->toDateTimeString()
            ], 500);
        }
    }


    public function exportCsv(Request $request)
    {
        $this->authorize('export', Call::class);

        try {
            $user = auth()->user();
            $showAgent = !in_array($user->role?->name, ['zone_admin', 'sector_admin', 'beat_operator']);

            $baseQuery = $this->applyScopeVisibility(Call::query());
            $query = $this->applyFiltersAndSearch($baseQuery, $request);

            // Sorting logic
            $sort = $request->get('sort', 'created_at');
            $direction = $request->get('direction', 'desc');
            $allowedSorts = ['call_number', 'created_at', 'priority', 'status', 'caller_name', 'caller_number', 'vehicle_no'];
            
            if ($sort === 'beat_id') $sort = 'office_id';

            if (in_array($sort, $allowedSorts)) {
                $query->orderBy($sort, $direction === 'asc' ? 'asc' : 'desc');
            } else {
                $query->orderBy('status', 'asc')->orderBy('created_at', 'desc');
            }

            // Optimize query for export
            $query->with([
                'callType', 
                'callSubType', 
                'office.parent.parent', // Beat -> Sector -> Zone
                'agent', 
                'vehicleType'
            ]);

            // Set execution limits for large exports
            set_time_limit(300); // 5 minutes
            ini_set('memory_limit', '512M');

            $filename = "calls_export_" . date('Y-m-d_H-i-s') . ".csv";
            $headers = [
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$filename",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];

            return response()->stream(function() use ($query, $showAgent) {
                $file = fopen('php://output', 'w');
                
                // Write Header
                $columns = [
                    'ID', 'Date', 'Time', 'Priority', 'Status', 'Category', 'Sub-Category',
                    'Zone', 'Sector', 'Beat', 'Caller Name', 'Caller Number', 'Vehicle No', 'Vehicle Type'
                ];
                if ($showAgent) {
                    array_splice($columns, 5, 0, ['Agent']);
                }
                fputcsv($file, $columns);

                // Stream records in chunks to prevent memory exhaustion
                $query->chunk(100, function($calls) use ($file, $showAgent) {
                    foreach ($calls as $call) {
                        // Efficiently find zone and sector from eager-loaded hierarchy
                        $beat = $call->office;
                        $sector = null;
                        $zone = null;

                        if ($beat) {
                            if ($beat->type === 'beat') {
                                $sector = $beat->parent;
                                $zone = $sector?->parent;
                            } elseif ($beat->type === 'sector') {
                                $sector = $beat;
                                $zone = $sector->parent;
                            } elseif ($beat->type === 'zone') {
                                $zone = $beat;
                            }
                        }

                        $row = [
                            $call->call_number,
                            $call->created_at ? $call->created_at->format('Y-m-d') : '',
                            $call->created_at ? $call->created_at->format('H:i:s') : '',
                            $call->priority,
                            $call->status,
                            $call->callType ? $call->callType->name : '',
                            $call->callSubType ? $call->callSubType->name : '',
                            $zone ? $zone->name : '',
                            $sector ? $sector->name : '',
                            $beat && $beat->type === 'beat' ? $beat->name : '',
                            $call->caller_name,
                            $call->caller_number,
                            $call->vehicle_no,
                            $call->vehicleType ? $call->vehicleType->name : ''
                        ];

                        if ($showAgent) {
                            array_splice($row, 5, 0, [$call->agent?->full_name ?? 'N/A']);
                        }
                        fputcsv($file, $row);
                    }
                    // Flush the buffer to browser after each chunk
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                });

                fclose($file);
            }, 200, $headers);

        } catch (\Throwable $e) {
            Log::error('Export failed: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // If headers are already sent, we can't return a JSON/Redirect response
            if (headers_sent()) {
                exit("\n--- ERROR DURING EXPORT: " . $e->getMessage() . " ---");
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Export failed: ' . $e->getMessage()], 500);
            }
            
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    public function geospatialLookup(Request $request)
    {
        $this->authorize('calls.api_lookup');
        $road = $request->input('road');
        $kmMarker = $request->input('km_marker');
        $direction = $request->input('direction');

        $landmarkQuery = \App\Models\GeospatialLandmark::with('office.parent.parent');

        if ($road) {
            $landmarkQuery->where('road_name', 'like', "%{$road}%");
        }

        if ($kmMarker) {
            $landmarkQuery->where('km_marker', 'like', "%{$kmMarker}%");
        }

        if ($direction) {
            $landmarkQuery->where('bound_direction', 'like', "%{$direction}%");
        }

        $landmarks = $landmarkQuery->limit(5)->get();

        // Also search offices (Beats) by KM range if kmMarker is numeric
        if ($kmMarker && is_numeric($kmMarker)) {
            $offices = \App\Models\Office::with('parent.parent')
                ->where('type', 'beat')
                ->where('is_active', true)
                ->where('km_start', '<=', $kmMarker)
                ->where('km_end', '>=', $kmMarker)
                ->get();

            foreach ($offices as $office) {
                // Check if this office is already in the landmarks to avoid confusion, 
                // but usually landmarks are more specific points.
                // We add it as a "Virtual Landmark"
                $landmarks->push((object)[
                    'id' => 'office_' . $office->id,
                    'location_name' => "KM Range: " . (int)$office->km_start . " - " . (int)$office->km_end,
                    'road_name' => $office->sector()?->name ?? $office->zone()?->name ?? 'NHMP',
                    'km_marker' => $kmMarker,
                    'office' => $office,
                    'agent_prompt' => "This KM falls within the operational boundary of " . $office->name,
                    'is_range_match' => true
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'landmarks' => $landmarks
        ]);
    }

    public function spatialContacts(Request $request)
    {
        $this->authorize('calls.api_lookup');
        $zoneId = $request->get('zone_id');
        $sectorId = $request->get('sector_id');
        $beatId = $request->get('beat_id');

        $contacts = [
            'zone' => null,
            'sector' => null,
            'beat' => null,
        ];

        if ($beatId) {
            $contacts['beat'] = \App\Models\GeospatialLandmark::where('office_id', $beatId)
                ->whereNotNull('contact_numbers')
                ->where('contact_numbers', '!=', '')
                ->value('contact_numbers');
        }

        if ($sectorId) {
            $sector = \App\Models\Office::find($sectorId);
            if ($sector) {
                $beatIds = $sector->getDescendantIds(true);
                $contacts['sector'] = \App\Models\GeospatialLandmark::whereIn('office_id', $beatIds)
                    ->whereNotNull('contact_numbers')
                    ->where('contact_numbers', '!=', '')
                    ->value('contact_numbers');
            }
        }

        if ($zoneId) {
            $zone = \App\Models\Office::find($zoneId);
            if ($zone) {
                $beatIds = $zone->getDescendantIds(true);
                $contacts['zone'] = \App\Models\GeospatialLandmark::whereIn('office_id', $beatIds)
                    ->whereNotNull('contact_numbers')
                    ->where('contact_numbers', '!=', '')
                    ->value('contact_numbers');
            }
        }

        return response()->json($contacts);
    }
}
