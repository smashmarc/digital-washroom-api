<?php

namespace App\Policies;

use App\Models\Evaluation;
use App\Models\User;
use App\Constants\PermissionConstant;
use App\Constants\Role as RoleConstant;

class EvaluationPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(RoleConstant::ADMINISTRATOR)) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::EVALUATION_VIEW);
    }

    public function view(User $user, Evaluation $model): bool
    {
        if ($model->user_id === $user->id) {
            return $user->hasPermissionTo(PermissionConstant::EVALUATION_VIEW_HISTORY);            
        }

        return $user->hasPermissionTo(PermissionConstant::EVALUATION_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionConstant::EVALUATION_CREATE);
    }

    public function update(User $user, Evaluation $model): bool
    {
        // Users with update.own can update evaluations where they are the assigned evaluator.
        if ($user->hasPermissionTo(PermissionConstant::EVALUATION_UPDATE_OWN) && $model->evaluator_id === $user->id) {
            return true;
        }

        return $user->hasPermissionTo(PermissionConstant::EVALUATION_UPDATE);
    }

    public function delete(User $user, Evaluation $model): bool
    {
        return $user->hasPermissionTo(PermissionConstant::EVALUATION_DELETE);
    }
}
