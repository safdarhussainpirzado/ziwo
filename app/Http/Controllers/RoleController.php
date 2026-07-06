<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $this->authorize('roles.view');
        $roles = \App\Models\Role::with('permissions')->get();
        // Group permissions by their prefix (e.g. users.view -> users)
        $permissions = \App\Models\Permission::all()->groupBy(function($perm) {
            return explode('.', $perm->name)[0];
        });
        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $this->authorize('roles.create');
        $validated = $request->validate([
            'name'        => 'required|unique:roles,name|regex:/^[a-z_]+$/',
            'display_name'=> 'required|string|max:100',
            'scope_level' => 'required|in:national,zone,sector,beat,call_centre',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = \App\Models\Role::create([
            'name'         => $validated['name'],
            'display_name' => $validated['display_name'],
            'scope_level'  => $validated['scope_level'],
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'role' => $role->load('permissions')]);
        }

        return redirect()->back()->with('success', 'Role created successfully.');
    }

    public function update(Request $request, \App\Models\Role $role)
    {
        $this->authorize('roles.update');
        $validated = $request->validate([
            'name'          => 'required|unique:roles,name,' . $role->id . '|regex:/^[a-z_]+$/',
            'display_name'  => 'required|string|max:100',
            'scope_level'   => 'required|in:national,zone,sector,beat,call_centre',
            'permissions'   => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name'         => $validated['name'],
            'display_name' => $validated['display_name'],
            'scope_level'  => $validated['scope_level'],
        ]);

        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        } else {
            $role->permissions()->detach();
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'role' => $role->load('permissions')]);
        }

        return redirect()->back()->with('success', 'Role updated successfully.');
    }

    public function toggleStatus(\App\Models\Role $role)
    {
        $this->authorize('roles.update');
        $role->status = ($role->status === 'active' ? 'inactive' : 'active');
        $role->save();

        return response()->json(['success' => true, 'status' => $role->status]);
    }

    public function destroy(\App\Models\Role $role)
    {
        $this->authorize('roles.delete');
        // Prevent deletion if any users are still assigned to this role
        if ($role->users()->exists()) {
            $count = $role->users()->count();
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete: {$count} user(s) are assigned to this role. Reassign them first.",
                ], 422);
            }
            return redirect()->back()->with('error', "Cannot delete: {$count} user(s) assigned to this role.");
        }

        $role->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Role deleted.');
    }
}
