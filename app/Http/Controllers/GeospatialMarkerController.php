<?php

namespace App\Http\Controllers;

use App\Models\GeospatialLandmark;
use App\Models\Office;
use Illuminate\Http\Request;

class GeospatialMarkerController extends Controller
{
    public function index(Request $request)
    {
        $perPage  = (int) $request->input('per_page', 15);
        $sortBy   = in_array($request->input('sort_by'), ['km_numeric', 'road_name', 'location_name']) ? $request->input('sort_by') : 'km_numeric';
        $sortDir  = $request->input('sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $query = GeospatialLandmark::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('location_name', 'LIKE', "%{$search}%")
                  ->orWhere('road_name',   'LIKE', "%{$search}%")
                  ->orWhere('beat_name',   'LIKE', "%{$search}%")
                  ->orWhere('zone_name',   'LIKE', "%{$search}%")
                  ->orWhere('nearby_cities', 'LIKE', "%{$search}%");
            });
        }

        if ($zoneId = $request->input('zone_id')) {
            $query->where('zone_name', 'LIKE', "%{$zoneId}%");
        }

        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where(function($q) {
                    $q->whereNull('agent_prompt')->orWhere('agent_prompt', 'NOT LIKE', '[INACTIVE]%');
                });
            } elseif ($status === 'inactive') {
                $query->where('agent_prompt', 'LIKE', '[INACTIVE]%');
            } elseif ($status === 'motorway') {
                $query->where('road_name', 'LIKE', 'M-%');
            } elseif ($status === 'highway') {
                $query->where('road_name', 'LIKE', 'N-%');
            }
        }

        $markers = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

        $zones = GeospatialLandmark::select('zone_name')->distinct()->whereNotNull('zone_name')->pluck('zone_name');

        $stats = [
            'total_markers'     => GeospatialLandmark::count(),
            'active_markers'    => GeospatialLandmark::where(function($q) {
                                        $q->whereNull('agent_prompt')->orWhere('agent_prompt', 'NOT LIKE', '[INACTIVE]%');
                                    })->count(),
            'inactive_markers'  => GeospatialLandmark::where('agent_prompt', 'LIKE', '[INACTIVE]%')->count(),
            'motorways'         => GeospatialLandmark::where('road_name', 'LIKE', 'M-%')->count(),
            'highways'          => GeospatialLandmark::where('road_name', 'LIKE', 'N-%')->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'items'      => $markers->items(),
                'pagination' => [
                    'current_page' => $markers->currentPage(),
                    'last_page'    => $markers->lastPage(),
                    'total'        => $markers->total(),
                    'per_page'     => $markers->perPage(),
                ],
                'stats' => $stats,
            ]);
        }

        $beats = Office::where('type', 'beat')->get();

        return view('operations.geospatial-markers.index', [
            'geospatialMarkers' => $markers,
            'zones'             => $zones,
            'beats'             => $beats,
            'stats'             => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'road_name'      => 'required|string|max:100',
            'location_name'  => 'required|string|max:255',
            'km_marker'      => 'nullable|string|max:20',
            'beat_id'        => 'nullable|exists:offices,id',
            'zone_id'        => 'nullable|exists:offices,id',
            'sector_id'      => 'nullable|exists:offices,id',
            'nearby_cities'  => 'nullable|string|max:255',
            'fuel_station'   => 'nullable|string|max:100',
            'contact_numbers'=> 'nullable|string|max:255',
        ]);

        $km = $validated['km_marker'] ?? null;

        // Resolve zone/sector/beat names from IDs
        $beatOffice   = $validated['beat_id']   ? Office::find($validated['beat_id'])   : null;
        $sectorOffice = $validated['sector_id'] ? Office::find($validated['sector_id']) : null;
        $zoneOffice   = $validated['zone_id']   ? Office::find($validated['zone_id'])   : null;

        $marker = GeospatialLandmark::create([
            'office_id'       => $validated['beat_id'] ?? 1,
            'road_name'       => $validated['road_name'],
            'location_name'   => $validated['location_name'],
            'km_marker'       => $km,
            'km_numeric'      => is_numeric($km) ? (float)$km : null,
            'beat_name'       => $beatOffice?->name,
            'sector_name'     => $sectorOffice?->name,
            'zone_name'       => $zoneOffice?->name,
            'nearby_cities'   => $validated['nearby_cities'] ?? null,
            'fuel_station'    => $validated['fuel_station']  ?? null,
            'contact_numbers' => $validated['contact_numbers'] ?? null,
            'agent_prompt'    => 'Contextual alert: Operative should verify if caller is near ' . $validated['location_name'] . ($km ? " (KM {$km})" : ''),
        ]);

        return response()->json(['success' => true, 'marker' => $marker]);
    }

    public function update(Request $request, GeospatialLandmark $geospatialMarker)
    {
        $validated = $request->validate([
            'road_name'      => 'required|string|max:100',
            'location_name'  => 'required|string|max:255',
            'km_marker'      => 'nullable|string|max:20',
            'beat_id'        => 'nullable|exists:offices,id',
            'zone_id'        => 'nullable|exists:offices,id',
            'sector_id'      => 'nullable|exists:offices,id',
            'nearby_cities'  => 'nullable|string|max:255',
            'fuel_station'   => 'nullable|string|max:100',
            'contact_numbers'=> 'nullable|string|max:255',
        ]);

        $km = $validated['km_marker'] ?? null;

        $beatOffice   = $validated['beat_id']   ? Office::find($validated['beat_id'])   : null;
        $sectorOffice = $validated['sector_id'] ? Office::find($validated['sector_id']) : null;
        $zoneOffice   = $validated['zone_id']   ? Office::find($validated['zone_id'])   : null;

        $geospatialMarker->update([
            'office_id'       => $validated['beat_id'] ?? $geospatialMarker->office_id,
            'road_name'       => $validated['road_name'],
            'location_name'   => $validated['location_name'],
            'km_marker'       => $km,
            'km_numeric'      => is_numeric($km) ? (float)$km : null,
            'beat_name'       => $beatOffice?->name   ?? $geospatialMarker->beat_name,
            'sector_name'     => $sectorOffice?->name ?? $geospatialMarker->sector_name,
            'zone_name'       => $zoneOffice?->name   ?? $geospatialMarker->zone_name,
            'nearby_cities'   => $validated['nearby_cities']   ?? null,
            'fuel_station'    => $validated['fuel_station']     ?? null,
            'contact_numbers' => $validated['contact_numbers']  ?? null,
            'agent_prompt'    => 'Contextual alert: Operative should verify if caller is near ' . $validated['location_name'] . ($km ? " (KM {$km})" : ''),
        ]);

        return response()->json(['success' => true, 'marker' => $geospatialMarker->fresh()]);
    }

    public function destroy(GeospatialLandmark $geospatialMarker)
    {
        $geospatialMarker->delete();
        return response()->json(['success' => true]);
    }

    public function toggleStatus(GeospatialLandmark $geospatialMarker)
    {
        // Toggle the bound_direction field as an active/inactive flag
        // since geospatial_landmarks has no dedicated status column.
        // We repurpose a lightweight JSON metadata approach via agent_prompt prefix.
        $isCurrentlyActive = !str_starts_with($geospatialMarker->agent_prompt ?? '', '[INACTIVE]');
        
        if ($isCurrentlyActive) {
            $newPrompt = '[INACTIVE] ' . ltrim(str_replace('[INACTIVE] ', '', $geospatialMarker->agent_prompt ?? ''));
            $newStatus = 'inactive';
        } else {
            $newPrompt = ltrim(str_replace('[INACTIVE] ', '', $geospatialMarker->agent_prompt ?? ''));
            $newStatus = 'active';
        }

        $geospatialMarker->update(['agent_prompt' => $newPrompt]);

        return response()->json([
            'success' => true,
            'status'  => $newStatus,
        ]);
    }
}
