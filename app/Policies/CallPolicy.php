<?php

namespace App\Policies;

use App\Models\Call;
use App\Models\User;
use App\Models\Role;

class CallPolicy
{
    /**
     * Determine if the user can browse the call listing.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('calls.view');
    }

    /**
     * Determine if the user can create a call.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('calls.create');
    }

    /**
     * Determine if the user can view the specific call.
     */
    public function view(User $user, Call $call): bool
    {
        if (!$user->hasPermission('calls.view')) {
            return false;
        }
        return $this->checkAccess($user, $call);
    }

    /**
     * Determine if the user can update the call.
     */
    public function update(User $user, Call $call): bool
    {
        if (!$user->hasPermission('calls.update')) {
            return false;
        }
        return $this->checkAccess($user, $call);
    }

    /**
     * Determine if the user can delete the call.
     */
    public function delete(User $user, Call $call): bool
    {
        if (!$user->hasPermission('calls.delete')) {
            return false;
        }
        return $this->checkAccess($user, $call);
    }

    /**
     * Determine if the user can export calls.
     */
    public function export(User $user): bool
    {
        // Allow Super Admin and Supervisor by permission
        if ($user->hasPermission('calls.export')) {
            return true;
        }

        // Also allow operational admins/operators to export their scoped data
        return in_array($user->role?->name, ['zone_admin', 'sector_admin', 'beat_operator']);
    }

    /**
     * Internal check for operational scope compatibility.
     */
    private function checkAccess(User $user, Call $call): bool
    {
        // 1. Supervisor always has access if they have the base permission
        if ($user->role?->name === 'agent_supervisor') {
            return true;
        }

        // 2. Agents only have access to their own calls
        if ($user->role?->name === 'agent') {
            return $call->agent_id === $user->id;
        }

        // 3. Organizational Scope Enforcement
        $scopes = $user->activeScopes;
        
        if ($scopes && $scopes->isNotEmpty()) {
            foreach ($scopes as $scope) {
                // If office_id is null, it means full access to all units of that type (or national)
                if (!$scope->office_id) {
                    return true;
                }
                
                // Get all descendant IDs of the scoped office
                $accessibleOfficeIds = $scope->office->getDescendantIds(true);
                
                if (in_array($call->office_id, $accessibleOfficeIds)) {
                    return true;
                }
            }
        }

        return false;
    }
}
