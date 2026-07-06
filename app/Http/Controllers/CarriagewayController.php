<?php

namespace App\Http\Controllers;

use App\Models\Carriageway;
use Illuminate\Http\Request;

class CarriagewayController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('geography.carriageways.view');
        $query = Carriageway::query();

        // Since the list of roads is small, we'll return all for client-side logic stability
        $carriageways = $query->get();

        if ($request->wantsJson()) {
            return response()->json([
                'items' => $carriageways
            ]);
        }

        return view('operations.carriageways.index', compact('carriageways'));
    }

    public function store(Request $request)
    {
        $this->authorize('geography.carriageways.manage');
        $validated = $request->validate([
            'type' => 'required|string|in:Motorway,Highway,Strategic Route',
            'road' => 'required|string|max:10',
            'road_short' => 'nullable|string|max:10',
            'road_name' => 'required|string|max:100',
            'road_from' => 'nullable|string|max:50',
            'road_to' => 'nullable|string|max:50',
            'total_km' => 'nullable|numeric',
        ]);

        $carriageway = Carriageway::create($validated);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'carriageway' => $carriageway
            ]);
        }

        return back()->with('success', 'Carriageway successfully added to the national grid.');
    }

    public function update(Request $request, Carriageway $carriageway)
    {
        $this->authorize('geography.carriageways.manage');
        $validated = $request->validate([
            'type' => 'required|string|in:Motorway,Highway,Strategic Route',
            'road' => 'required|string|max:10',
            'road_short' => 'nullable|string|max:10',
            'road_name' => 'required|string|max:100',
            'road_from' => 'nullable|string|max:50',
            'road_to' => 'nullable|string|max:50',
            'total_km' => 'nullable|numeric',
        ]);

        $carriageway->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'carriageway' => $carriageway
            ]);
        }

        return back()->with('success', 'Carriageway configuration updated.');
    }

    public function toggleStatus(Carriageway $carriageway)
    {
        $this->authorize('geography.carriageways.manage');
        $carriageway->status = ($carriageway->status === 'active' ? 'inactive' : 'active');
        $carriageway->save();

        return response()->json([
            'success' => true,
            'status' => $carriageway->status
        ]);
    }

    public function destroy(Carriageway $carriageway)
    {
        $this->authorize('geography.carriageways.manage');
        $carriageway->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', 'Carriageway purged from system.');
    }
}
