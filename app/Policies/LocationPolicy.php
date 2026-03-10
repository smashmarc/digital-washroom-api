<?php

namespace App\Policies;

use App\Constants\PermissionConstant;
use App\Constants\Role as RoleConstant;
use App\Models\Location;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class LocationPolicy
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
        return $user?->hasRole(RoleConstant::ADMINISTRATOR) ? true : null;
    }

    /**
     * Check if the user can view locations.
     */
    public function view(Location $model): bool
    {
        return $this->entraUser()?->hasPermissionTo(PermissionConstant::LOCATION_VIEW) ?? false;
    }

    /**
     * Check if the user can create locations.
     */
    public function create(): bool
    {
        return $this->entraUser()?->hasPermissionTo(PermissionConstant::LOCATION_CREATE) ?? false;
    }

    /**
     * Check if the user can update locations.
     */
    public function update(Location $model): bool
    {
        return $this->entraUser()?->hasPermissionTo(PermissionConstant::LOCATION_UPDATE) ?? false;
    }

    /**
     * Check if the user can delete locations.
     */
    public function delete(Location $model): bool
    {
        return $this->entraUser()?->hasPermissionTo(PermissionConstant::LOCATION_DELETE) ?? false;
    }
}
