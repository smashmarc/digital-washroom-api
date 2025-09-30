<?php

namespace App\Policies;

use App\Models\User;
use App\Constants\PermissionConstant;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Constants\Role;

class PermissionPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(Role::ADMINISTRATOR)) {
            return true;
        }

        return null; 
    }

    public function manage(User $user): bool
    {
        return $user->hasRole(Role::ADMINISTRATOR);
    }

}
