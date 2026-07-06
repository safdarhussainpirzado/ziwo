<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    /**
     * Display a listing of offices filtered by type.
     */
    public function index(Request $request)
    {
        $this->authorize('geography.offices.view');

        $offices = Office::with(['parent', 'children'])->get();
        
        $regions = $offices->where('type', 'region')->values();
        $zones = $offices->where('type', 'zone')->values();
        $sectors = $offices->where('type', 'sector')->values();

        return view('operations.offices.index', compact('offices', 'regions', 'zones', 'sectors'));
    }

    /**
     * Store a newly created office.
     */
    public function store(Request $request)
    {
        $this->authorize('geography.offices.create');

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'type' => 'required|in:region,zone,sector,beat',
            'parent_id' => 'nullable|exists:offices,id',
            'operational_type' => 'nullable|string|max:50',
        ]);

        $office = Office::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'office' => $office]);
        }

        return back()->with('success', ucfirst($validated['type']) . ' successfully created.');
    }

    /**
     * Update the specified office.
     */
    public function update(Request $request, Office $office)
    {
        $this->authorize('geography.offices.update');

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'parent_id' => 'nullable|exists:offices,id',
            'operational_type' => 'nullable|string|max:50',
        ]);

        $office->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'office' => $office]);
        }

        return back()->with('success', 'Office configuration updated.');
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(Office $office)
    {
        $this->authorize('geography.offices.update');
        $office->is_active = !$office->is_active;
        $office->save();

        return response()->json([
            'success' => true,
            'is_active' => $office->is_active
        ]);
    }

    /**
     * Remove the office from the system.
     */
    public function destroy(Office $office)
    {
        $this->authorize('geography.offices.delete');
        
        // Prevent deletion if it has children
        if ($office->children()->count() > 0) {
            return response()->json([
                'success' => false, 
                'message' => 'Cannot delete office with sub-units. Delete children first.'
            ], 422);
        }

        $office->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Office removed successfully.');
    }
}
