<?php

namespace App\Policies;

use App\Constants\PermissionConstant;
use App\Constants\Role as RoleConstant;
use App\Models\User;

class UserPolicy
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
       
        return $user->hasPermissionTo(PermissionConstant::USER_VIEW);
    }

    public function create(User $user): bool
    {
       
        return $user->hasPermissionTo(PermissionConstant::USER_CREATE);
    }

    public function update(User $user, ?User $model = null): bool
    {
        if ($model?->hasRole(RoleConstant::ADMINISTRATOR)) {
            return false;
        }
        return $user->hasPermissionTo(PermissionConstant::USER_UPDATE);
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }
        if ($model->hasRole(RoleConstant::ADMINISTRATOR)) {
            return false;
        }
        return $user->hasPermissionTo(PermissionConstant::USER_DELETE);
    }
}
