<?php

namespace App\Policies;

use App\Constants\PermissionConstant;
use App\Constants\Role as RoleConstant;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;

class RolePolicy
{

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
        return $user?->hasRole(RoleConstant::ADMINISTRATOR) ? true : null;
    }

    /**
     * Check if the user can view roles.
     */
    public function view(): bool
    {
        return $this->entraUser()?->hasPermissionTo(PermissionConstant::ROLE_VIEW) ?? false;
    }

    /**
     * Check if the user can create roles.
     */
    public function create(): bool
    {
        return $this->entraUser()?->hasPermissionTo(PermissionConstant::ROLE_CREATE) ?? false;
    }

    /**
     * Check if the user can update a role.
     */
    public function update(Role $model): bool
    {
        return $this->entraUser()?->hasPermissionTo(PermissionConstant::ROLE_UPDATE) ?? false;
    }

    /**
     * Check if the user can delete a role.
     */
    public function delete(Role $model): bool
    {
        return $this->entraUser()?->hasPermissionTo(PermissionConstant::ROLE_DELETE) ?? false;
    }
}
