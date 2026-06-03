<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserAssignment;
use App\Constants\PermissionConstant;
use App\Constants\Role as RoleConstant;

class UserAssignmentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(RoleConstant::ADMINISTRATOR)) {
            return true;
        }

        return null;
    }

    public function view(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::USER_ASSIGNMENT_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::USER_ASSIGNMENT_CREATE);
    }

    public function update(User $user, UserAssignment $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::USER_ASSIGNMENT_UPDATE);
    }

    public function delete(User $user, UserAssignment $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::USER_ASSIGNMENT_DELETE);
    }
}
