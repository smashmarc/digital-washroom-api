<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;
use App\Constants\Role;
use App\Constants\PermissionConstant;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnitPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(Role::ADMINISTRATOR)) {
            return true;
        }
        return null;
    }

    public function view(User $user, Unit $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::UNIT_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::UNIT_CREATE);
    }

    public function update(User $user, Unit $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::UNIT_UPDATE);
    }

    public function delete(User $user, Unit $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::UNIT_DELETE);
    }
}
