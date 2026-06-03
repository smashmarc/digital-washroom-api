<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use App\Constants\PermissionConstant;
use App\Constants\Role as RoleConstant;

class QuestionPolicy
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
        return $user->hasPermissionTo(PermissionConstant::QUESTION_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::QUESTION_CREATE);
    }

    public function update(User $user, Question $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::QUESTION_UPDATE);
    }

    public function delete(User $user, Question $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::QUESTION_DELETE);
    }
}
