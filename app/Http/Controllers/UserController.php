<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserScope;
use App\Models\Role;
use App\Models\Designation;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /* ─────────────────────────────────────────────────────────────────── */
    /*  User CRUD                                                           */
    /* ─────────────────────────────────────────────────────────────────── */

    public function index()
    {
        $this->authorize('viewAny', User::class);
        $users        = User::with(['role.permissions', 'designation', 'activeScopes.office'])->get();
        $roles        = Role::all();
        $designations = Designation::where('is_active', true)->orderByDesc('bps')->orderBy('sort_order')->get(['id', 'name', 'bps', 'short_code']);
        $zones        = Office::zones()->where('is_active', true)->get(['id', 'name']);
        $sectors      = Office::sectors()->where('is_active', true)->get(['id', 'name', 'parent_id as zone_id']);
        $beats        = Office::beats()->where('is_active', true)->get(['id', 'name', 'parent_id as sector_id']);

        return view('operations.users.index', compact('users', 'roles', 'designations', 'zones', 'sectors', 'beats'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        $this->enforceOperationAdminRestrictions($request);

        $validated = $request->validate([
            'username'       => ['required', 'string', 'max:80',  'unique:users'],
            'full_name'      => ['required', 'string', 'max:150'],
            'cnic'           => ['required', 'string', 'regex:/^[0-9]{5}-[0-9]{7}-[0-9]{1}$/', 'unique:users'],
            'email'          => ['required', 'string', 'email',   'max:100', 'unique:users'],
            'mobile_no'      => ['required', 'string', 'regex:/^03[0-9]{9}$/', 'unique:users'],
            'designation_id' => ['required', 'exists:designations,id'],
            'password'       => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id'        => ['required', 'exists:roles,id'],
        ]);

        $user = User::create($validated);

        // Attach initial scopes if provided
        if ($request->has('scopes')) {
            $this->syncScopes($user, $request->input('scopes', []));
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'user' => $user->load(['role', 'activeScopes'])]);
        }

        return redirect()->route('operations.users.index')->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $this->enforceOperationAdminRestrictions($request);

        $validated = $request->validate([
            'username'       => ['required', 'string', 'max:80',  'unique:users,username,' . $user->id],
            'full_name'      => ['required', 'string', 'max:150'],
            'cnic'           => ['required', 'string', 'regex:/^[0-9]{5}-[0-9]{7}-[0-9]{1}$/', 'unique:users,cnic,' . $user->id],
            'email'          => ['required', 'string', 'email',   'max:100', 'unique:users,email,' . $user->id],
            'mobile_no'      => ['required', 'string', 'regex:/^03[0-9]{9}$/', 'unique:users,mobile_no,' . $user->id],
            'designation_id' => ['required', 'exists:designations,id'],
            'password'       => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role_id'        => ['required', 'exists:roles,id'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        // Sync scopes if provided in request
        if ($request->has('scopes')) {
            $this->syncScopes($user, $request->input('scopes', []));
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'user' => $user->load(['role', 'activeScopes'])]);
        }

        return back()->with('success', 'User configuration updated.');
    }

    public function toggleStatus(User $user)
    {
        $this->authorize('manageStatus', $user);

        // Prevent disabling own account
        abort_if($user->id === auth()->id(), 403, 'You cannot deactivate your own account.');

        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'status'  => $user->is_active ? 'active' : 'inactive',
        ]);
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->authorize('managePassword', $user);
        $this->enforceOperationAdminRestrictions($request);

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Store old password in history
        \App\Models\PasswordHistory::create([
            'user_id'  => $user->id,
            'password' => $user->password, // store previously hashed value
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return response()->json(['success' => true, 'message' => 'Password reset successfully.']);
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Prevent self-deletion
        abort_if($user->id === auth()->id(), 403, 'You cannot delete your own account.');

        // Remove all scope assignments first to avoid FK issues
        $user->scopes()->delete();

        $user->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'User removed from registry.');
    }

    /* ─────────────────────────────────────────────────────────────────── */
    /*  Scope Management — AJAX endpoints                                   */
    /* ─────────────────────────────────────────────────────────────────── */

    /**
     * GET /admin/users/{user}/scopes
     * Returns all active scopes for a user as JSON (for admin UI).
     */
    public function getScopes(User $user)
    {
        $this->authorize('manageScopes', $user);

        $scopes = $user->scopes()->with('office')->get()->map(function ($scope) {
            return [
                'id'           => $scope->id,
                'office_id'    => $scope->office_id,
                'unit_type'    => $scope->office?->type ?? 'national',
                'unit_type_label' => $scope->office ? ucfirst($scope->office->type) : 'National',
                'unit_id'      => $scope->office_id,
                'access_level' => $scope->access_level,
                'access_label' => UserScope::ACCESS_LEVELS[$scope->access_level] ?? $scope->access_level,
                'label'        => $scope->label,
                'display_name' => $scope->display_name,
                'is_active'    => $scope->is_active,
            ];
        });

        return response()->json(['scopes' => $scopes]);
    }

    /**
     * POST /admin/users/{user}/scopes
     * Adds a single scope to a user.
     */
    public function addScope(Request $request, User $user)
    {
        $this->authorize('manageScopes', $user);
        $this->enforceOperationAdminRestrictions($request);

        $validated = $request->validate([
            'office_id'    => ['nullable', 'exists:offices,id'],
            'access_level' => ['required', 'in:read_only,read_write,full'],
            'label'        => ['nullable', 'string', 'max:100'],
        ]);

        /* 
        // Enforce exclusivity for specific units if active
        if ($validated['office_id']) {
            $alreadyAssigned = UserScope::where('office_id', $validated['office_id'])
                ->where('user_id', '!=', $user->id)
                ->where('is_active', true)
                ->exists();
            abort_if($alreadyAssigned, 422, "This office is already assigned to another user.");
        }
        */

        $scope = $user->scopes()->updateOrCreate(
            ['office_id' => $validated['office_id']],
            ['access_level' => $validated['access_level'], 'label' => $validated['label'] ?? null, 'is_active' => true]
        );

        return response()->json([
            'success'      => true,
            'scope'        => $scope,
            'display_name' => $scope->display_name,
        ]);
    }

    /**
     * PATCH /admin/users/{user}/scopes/{scope}
     * Updates access_level or label on an existing scope.
     */
    public function updateScope(Request $request, User $user, UserScope $scope)
    {
        $this->authorize('manageScopes', $user);

        abort_if($scope->user_id !== $user->id, 404);

        $validated = $request->validate([
            'access_level' => ['required', 'in:read_only,read_write,full'],
            'label'        => ['nullable', 'string', 'max:100'],
            'is_active'    => ['boolean'],
        ]);

        $scope->update($validated);

        return response()->json(['success' => true, 'scope' => $scope, 'display_name' => $scope->display_name]);
    }

    /**
     * DELETE /admin/users/{user}/scopes/{scope}
     * Removes a scope from a user.
     */
    public function removeScope(User $user, UserScope $scope)
    {
        $this->authorize('manageScopes', $user);

        abort_if($scope->user_id !== $user->id, 404);

        $scope->delete();

        return response()->json(['success' => true]);
    }

    /* ─────────────────────────────────────────────────────────────────── */
    /*  Private Helpers                                                     */
    /* ─────────────────────────────────────────────────────────────────── */

    /**
     * Sync scopes from an array payload.
     * Expected format: [['unit_type'=>'zone','unit_id'=>3,'access_level'=>'read_only'], ...]
     */
    private function syncScopes(User $user, array $scopes): void
    {
        // Remove all existing scopes then re-insert
        $user->scopes()->delete();

        foreach ($scopes as $scopeData) {
            $officeId = $scopeData['office_id'] ?? $scopeData['unit_id'] ?? null;
            $access   = $scopeData['access_level'] ?? null;

            if (!$access) continue;

            /* 
            // Enforce exclusivity - REMOVED: High-level offices like HQ can be shared by multiple admins
            if ($officeId) {
                $alreadyAssigned = UserScope::where('office_id', $officeId)
                    ->where('user_id', '!=', $user->id)
                    ->where('is_active', true)
                    ->exists();
                if ($alreadyAssigned) {
                    abort(422, "Conflict: Office (ID: $officeId) is already assigned to another user.");
                }
            }
            */

            $user->scopes()->create([
                'office_id'    => $officeId,
                'access_level' => $access,
                'label'        => $scopeData['label'] ?? null,
                'is_active'    => true,
            ]);
        }
    }

    /**
     * Validate that the given unit_id exists in the correct table.
     */
    private function validateUnitId(string $unitType, ?int $unitId): void
    {
        if ($unitId === null) return; // null = all of type — always valid

        $exists = match ($unitType) {
            'zone'   => Zone::where('id', $unitId)->exists(),
            'sector' => Sector::where('id', $unitId)->exists(),
            'beat'   => Beat::where('id', $unitId)->exists(),
            default  => true, // national / call_centre don't need a unit_id
        };

        abort_if(!$exists, 422, "Unit ID {$unitId} does not exist for type '{$unitType}'.");
    }

    /**
     * Enforce restrictions for the Operation Admin role.
     */
    private function enforceOperationAdminRestrictions(Request $request): void
    {
        $currentUser = auth()->user();
        if (!$currentUser || $currentUser->role->name !== Role::IT_ADMIN) {
            return;
        }

        // 1. Role Restrictions: Cannot create/update to super_admin or operation_admin
        if ($request->has('role_id')) {
            $targetRole = Role::find($request->role_id);
            $restricted = [Role::SUPER_ADMIN, Role::IT_ADMIN];
            if ($targetRole && in_array($targetRole->name, $restricted)) {
                abort(403, 'You are not authorized to assign this role authority level.');
            }
        }

        if ($request->has('scopes')) {
            foreach ($request->input('scopes', []) as $scope) {
                $office = isset($scope['office_id']) ? Office::find($scope['office_id']) : null;
                if ($office && $office->type === 'region') { // Region is national-ish
                    abort(403, 'You are not authorized to assign high-level scope.');
                }
            }
        }
    }
}
