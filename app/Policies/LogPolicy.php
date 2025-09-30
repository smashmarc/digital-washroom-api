<?php

namespace App\Policies;

use App\Models\User;
use App\Constants\Role;
use App\Models\Location;
use App\Constants\PermissionConstant;
use App\Models\Log;
use Illuminate\Auth\Access\HandlesAuthorization;

class LogPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(Role::ADMINISTRATOR)) {
            return true;
        }

        return null; 
    }

    public function view(User $user, Log $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::LOG_VIEW);
    }

    public function create(User $user): bool
    {
       
        return $user->hasPermissionTo(PermissionConstant::LOG_CREATE);
    }

  

}
