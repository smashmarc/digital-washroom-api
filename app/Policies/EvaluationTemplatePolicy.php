<?php

namespace App\Policies;

use App\Models\EvaluationTemplate;
use App\Models\User;
use App\Constants\PermissionConstant;
use App\Constants\Role as RoleConstant;

class EvaluationTemplatePolicy
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
        // Users who can create or conduct evaluations need to read template criteria.
        return $user->hasPermissionTo(PermissionConstant::EVALUATION_TEMPLATE_VIEW)
            || $user->hasPermissionTo(PermissionConstant::EVALUATION_CREATE)
            || $user->hasPermissionTo(PermissionConstant::EVALUATION_UPDATE)
            || $user->hasPermissionTo(PermissionConstant::EVALUATION_UPDATE_OWN);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::EVALUATION_TEMPLATE_CREATE);
    }

    public function update(User $user, EvaluationTemplate $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::EVALUATION_TEMPLATE_UPDATE);
    }

    public function delete(User $user, EvaluationTemplate $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::EVALUATION_TEMPLATE_DELETE);
    }
}
