<?php

namespace App\Policies;

use App\Models\QuestionCategory;
use App\Models\User;
use App\Constants\PermissionConstant;
use App\Constants\Role as RoleConstant;

class QuestionCategoryPolicy
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
        return $user->hasPermissionTo(PermissionConstant::QUESTION_CATEGORY_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::QUESTION_CATEGORY_CREATE);
    }

    public function update(User $user, QuestionCategory $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::QUESTION_CATEGORY_UPDATE);
    }

    public function delete(User $user, QuestionCategory $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::QUESTION_CATEGORY_DELETE);
    }
}
