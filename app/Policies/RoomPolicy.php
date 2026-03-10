<?php

namespace App\Policies;

use App\Constants\PermissionConstant;
use App\Constants\Role as RoleConstant;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;

class RoomPolicy
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
     * Check if the user can view rooms.
     */
    public function view(): bool
    {
        return $this->entraUser()?->hasPermissionTo(PermissionConstant::ROOM_VIEW) ?? false;
    }

    /**
     * Check if the user can create rooms.
     */
    public function create(): bool
    {
        return $this->entraUser()?->hasPermissionTo(PermissionConstant::ROOM_CREATE) ?? false;
    }

    /**
     * Check if the user can update rooms.
     */
    public function update(): bool
    {
        return $this->entraUser()?->hasPermissionTo(PermissionConstant::ROOM_UPDATE) ?? false;
    }

    /**
     * Check if the user can delete rooms.
     */
    public function delete(): bool
    {
        return $this->entraUser()?->hasPermissionTo(PermissionConstant::ROOM_DELETE) ?? false;
    }
}
