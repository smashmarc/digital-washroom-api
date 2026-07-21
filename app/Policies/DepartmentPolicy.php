<?php

namespace App\Policies;

use App\Models\User;
use App\Constants\Role;
use App\Models\Department;
use App\Constants\PermissionConstant;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(Role::ADMINISTRATOR)) {
            return true;
        }
        return null;
    }

    public function view(User $user, Department $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::DEPARTMENT_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::DEPARTMENT_CREATE);
    }

    public function update(User $user, Department $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::DEPARTMENT_UPDATE);
    }

    public function delete(User $user, Department $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::DEPARTMENT_DELETE);
    }
}
