<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;

class UserPolicy
{
    /**
     * Common logic to determine if an admin can manage a target user.
     */
    private function canManage(User $admin, User $target): bool
    {
        // 1. Users can always manage their own data/password
        if ($admin->id === $target->id) {
            return true;
        }

        // 2. Super Admin can manage anyone else
        if ($admin->role?->name === Role::SUPER_ADMIN) {
            return true;
        }

        // 3. IT/Operation Admin (IT_ADMIN) restrictions:
        // They can manage lower roles, but NOT Super Admins or other IT Admins
        if ($admin->role?->name === Role::IT_ADMIN) {
            $restricted = [Role::SUPER_ADMIN, Role::IT_ADMIN];
            
            // Only allow if the target is NOT in the restricted roles
            return !in_array($target->role?->name, $restricted);
        }

        return false;
    }

    public function viewAny(User $admin): bool
    {
        return $admin->hasPermission('users.view');
    }

    public function create(User $admin): bool
    {
        return $admin->hasPermission('users.create');
    }

    public function update(User $admin, User $target): bool
    {
        return $admin->hasPermission('users.update') && $this->canManage($admin, $target);
    }

    public function delete(User $admin, User $target): bool
    {
        return $admin->hasPermission('users.delete') && $this->canManage($admin, $target);
    }

    public function manageStatus(User $admin, User $target): bool
    {
        return $admin->hasPermission('users.manage_status') && $this->canManage($admin, $target);
    }

    public function managePassword(User $admin, User $target): bool
    {
        return $admin->hasPermission('users.manage_password') && $this->canManage($admin, $target);
    }

    public function manageScopes(User $admin, User $target): bool
    {
        return $admin->hasPermission('users.manage_scopes') && $this->canManage($admin, $target);
    }
}
