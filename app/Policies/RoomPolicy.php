<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;
use App\Constants\PermissionConstant;
use App\Constants\Role as RoleConstant;

class RoomPolicy
{

    public function before(User $user, string $ability): ?bool
    {

        if ($user->hasRole(RoleConstant::ADMINISTRATOR)) {
            return true;
        }

        return null;
    }

    public function view(User $user, Room $model): bool
    {
        
        return $user->hasPermissionTo(PermissionConstant::ROOM_VIEW);
    }

    public function create(User $user): bool
    {

        return $user->hasPermissionTo(PermissionConstant::ROOM_CREATE);
    }

    public function update(User $user, ?Room $model = null): bool
    {
        if ($model?->id == 1) {
            return false;
        }
        return $user->hasPermissionTo(PermissionConstant::ROOM_UPDATE);
    }

    public function delete(User $user, Room $model): bool
    {
        if ($model->id == 1) {
            return false;
        }
        return $user->hasPermissionTo(PermissionConstant::ROOM_DELETE);
    }
}
