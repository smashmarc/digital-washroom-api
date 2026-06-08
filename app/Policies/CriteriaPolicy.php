<?php

namespace App\Policies;

use App\Models\Criteria;
use App\Models\User;
use App\Constants\PermissionConstant;
use App\Constants\Role as RoleConstant;

class CriteriaPolicy
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
        return $user->hasPermissionTo(PermissionConstant::CRITERIA_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::CRITERIA_CREATE);
    }

    public function update(User $user, Criteria $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::CRITERIA_UPDATE);
    }

    public function delete(User $user, Criteria $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::CRITERIA_DELETE);
    }
}
