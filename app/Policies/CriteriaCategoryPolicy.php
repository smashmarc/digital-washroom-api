<?php

namespace App\Policies;

use App\Models\CriteriaCategory;
use App\Models\User;
use App\Constants\PermissionConstant;
use App\Constants\Role as RoleConstant;

class CriteriaCategoryPolicy
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
        return $user->hasPermissionTo(PermissionConstant::CRITERIA_CATEGORY_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::CRITERIA_CATEGORY_CREATE);
    }

    public function update(User $user, CriteriaCategory $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::CRITERIA_CATEGORY_UPDATE);
    }

    public function delete(User $user, CriteriaCategory $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::CRITERIA_CATEGORY_DELETE);
    }
}
