<?php

namespace App\Policies;

use App\Models\User;
use App\Constants\PermissionConstant;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Constants\Role;

class UserPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(Role::SUPER_ADMIN)) {
            return true;
        }

        return null; 
    }


    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::USER_VIEW);
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::USER_VIEW);
    }

    public function create(User $user): bool
    {
        if ($user->hasRole(Role::SUPER_ADMIN)) {
            return true;
        }

        return $user->hasPermissionTo(PermissionConstant::USER_CREATE);
    }

    public function update(User $user, User $model): bool
    {
        if ($user->hasPermissionTo(PermissionConstant::USER_UPDATE)) {
            return $model->id === $user->id;
        }   
        return false;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::USER_DELETE);
    }
}
