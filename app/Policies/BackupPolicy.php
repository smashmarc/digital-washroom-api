<?php

namespace App\Policies;

use App\Constants\PermissionConstant;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BackupPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('administrator') ? true : null;
    }

    public function view(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::BACKUP_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::BACKUP_CREATE);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::BACKUP_DELETE);
    }

    public function download(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::BACKUP_DOWNLOAD);
    }

    public function fileDelete(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::BACKUP_FILE_DELETE);
    }
}
