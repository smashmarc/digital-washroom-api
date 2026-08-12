<?php

namespace App\Policies;

use App\Constants\PermissionConstant;
use App\Constants\Role as RoleConstant;
use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
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
        return $user->hasPermissionTo(PermissionConstant::ANNOUNCEMENT_VIEW);
    }

    public function viewOnDashboard(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::HOME_VIEW_ANNOUNCEMENTS);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::ANNOUNCEMENT_CREATE);
    }

    public function update(User $user, ?Announcement $model = null): bool
    {
        return $user->hasPermissionTo(PermissionConstant::ANNOUNCEMENT_UPDATE);
    }

    public function delete(User $user, ?Announcement $model = null): bool
    {
        return $user->hasPermissionTo(PermissionConstant::ANNOUNCEMENT_DELETE);
    }
}
