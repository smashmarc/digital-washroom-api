<?php

namespace App\Policies;

use App\Constants\PermissionConstant;
use App\Constants\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PermissionPolicy
{
    use HandlesAuthorization;

    /**
     * Get the current Entra user.
     */
    protected function entraUser(): ?User
    {
        return Auth::guard('entra')->user();
    }

    /**
     * Run before any policy check.
     * Admins bypass all checks automatically.
     */
    public function before(?User $user, string $ability): ?bool
    {
        $user = $this->entraUser();

        return $user?->hasRole(Role::ADMINISTRATOR) ? true : null;
    }

    /**
     * Check if a user can manage permissions.
     */
    public function manage(User $user): bool
    {
        $user = $this->entraUser();

        return $user?->hasRole(Role::ADMINISTRATOR) ?? false;
    }
}
