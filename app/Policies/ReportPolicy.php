<?php

namespace App\Policies;

use App\Models\User;
use App\Constants\Role;
use App\Constants\PermissionConstant;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(Role::ADMINISTRATOR)) {
            return true;
        }

        return null;
    }

    public function view(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::REPORT_VIEW);
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::REPORT_EXPORT);
    }
}
