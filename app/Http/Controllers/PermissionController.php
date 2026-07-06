<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $this->authorize('permissions.view');
        $permissions = Permission::all();
        // Group by module for the UI
        $groupedPermissions = $permissions->groupBy('module');
        
        return view('admin.permissions.index', compact('permissions', 'groupedPermissions'));
    }

    public function store(Request $request)
    {
        $this->authorize('permissions.create');
        $validated = $request->validate([
            'name' => 'required|unique:permissions,name',
            'display_name' => 'required',
            'module' => 'required',
            'description' => 'nullable'
        ]);

        $permission = Permission::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'permission' => $permission
            ]);
        }

        return redirect()->back()->with('success', 'New capability defined in the registry.');
    }

    public function update(Request $request, Permission $permission)
    {
        $this->authorize('permissions.update');
        $validated = $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id,
            'display_name' => 'required',
            'module' => 'required',
            'description' => 'nullable'
        ]);

        $permission->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'permission' => $permission
            ]);
        }

        return redirect()->back()->with('success', 'Capability parameters updated.');
    }

    public function toggleStatus(Permission $permission)
    {
        $this->authorize('permissions.update');
        $permission->status = ($permission->status === 'active' ? 'inactive' : 'active');
        $permission->save();

        return response()->json([
            'success' => true,
            'status' => $permission->status
        ]);
    }

    public function destroy(Permission $permission)
    {
        $this->authorize('permissions.delete');
        $permission->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', 'Capability purged from system.');
    }
}
