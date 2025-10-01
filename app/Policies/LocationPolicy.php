<?php

namespace App\Policies;

use App\Models\User;
use App\Constants\Role;
use App\Models\Location;
use App\Constants\PermissionConstant;
use Illuminate\Auth\Access\HandlesAuthorization;

class LocationPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(Role::ADMINISTRATOR)) {
            return true;
        }

        return null; 
    }

    public function view(User $user, Location $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::LOCATION_VIEW);
    }

    public function create(User $user): bool
    {
       
        return $user->hasPermissionTo(PermissionConstant::LOCATION_CREATE);
    }

    public function update(User $user, Location $model): bool
    {
        // if ($user->hasPermissionTo(PermissionConstant::USER_UPDATE)) {
        //     return $model->id === $user->id;
        // }   
        //return false;
        return $user->hasPermissionTo(PermissionConstant::LOCATION_UPDATE);
    }

    public function delete(User $user, Location $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::LOCATION_DELETE);
    }
}
