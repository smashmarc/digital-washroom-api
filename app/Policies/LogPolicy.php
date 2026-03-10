<?php

namespace App\Policies;

use App\Constants\PermissionConstant;
use App\Constants\Role as RoleConstant;
use App\Models\Log;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;


class LogPolicy
{
    use HandlesAuthorization;


    protected function entraUser(): ?User
    {
        return Auth::guard('entra')->user();
    }

    public function before(?User $user, string $ability): ?bool
    {
        $user = $this->entraUser();
        return $user?->hasRole(RoleConstant::ADMINISTRATOR) ? true : null;
    }

    public function view(?User $user, Log $model): bool
    {

        return $this->entraUser()?->hasPermissionTo(PermissionConstant::LOG_VIEW) ?? false;
    }

    public function create(?User $user): bool
    {       
        return $this->entraUser()?->hasPermissionTo(PermissionConstant::LOG_CREATE) ?? false;
    }
}
