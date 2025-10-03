<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Constants\PermissionConstant;
use App\Constants\Role as RoleConstant;

class RolePolicy
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
        
        return $user->hasPermissionTo(PermissionConstant::ROLE_VIEW);
    }

    public function create(User $user): bool
    {
       
        return $user->hasPermissionTo(PermissionConstant::ROLE_CREATE);
    }

    public function update(User $user, Role $model): bool
    {
        // if ($user->hasPermissionTo(PermissionConstant::USER_UPDATE)) {
        //     return $model->id === $user->id;
        // }   
        //return false;
        return $user->hasPermissionTo(PermissionConstant::ROLE_UPDATE);
    }

    public function delete(User $user, Role $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::ROLE_DELETE);
    }
}
