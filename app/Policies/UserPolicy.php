<?php

namespace App\Policies;

use App\Constants\PermissionConstant;
use App\Constants\Role as RoleConstant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserPolicy
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
     * Check if the user can view other users.
     */
    public function view(): bool
    {
        return $this->entraUser()?->hasPermissionTo(PermissionConstant::USER_VIEW) ?? false;
    }

    /**
     * Check if the user can create users.
     */
    public function create(): bool
    {
        return $this->entraUser()?->hasPermissionTo(PermissionConstant::USER_CREATE) ?? false;
    }

    /**
     * Check if the user can update users.
     */
    public function update(): bool
    {
        return $this->entraUser()?->hasPermissionTo(PermissionConstant::USER_UPDATE) ?? false;
    }

    /**
     * Check if the user can delete users.
     */
    public function delete(): bool
    {
        return $this->entraUser()?->hasPermissionTo(PermissionConstant::USER_DELETE) ?? false;
    }
}
