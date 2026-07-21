<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\UserAssignment;
use App\Models\EvaluationTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserAssignmentService extends BaseService
{
    public function __construct(UserAssignment $userAssignment)
    {
        parent::__construct($userAssignment);
    }

    public function searchPaginatedList(array $params = [])
    {
        $params['searchableColumns'] = ['status', 'notes', 'user.name', 'template.name'];
        return parent::list($params);
    }

    public function create(array $data): UserAssignment
    {
        DB::beginTransaction();

        try {
            $assignment = UserAssignment::create([
                'user_id'                => $data['user_id'],
                'evaluation_template_id' => $data['evaluation_template_id'],
                'assigned_by'            => auth()->id(),
                'due_date'               => $data['due_date'] ?? null,
                'status'                 => 'pending',
                'notes'                  => $data['notes'] ?? null,
            ]);

            DB::commit();
            return $assignment->load(['user', 'template', 'assigner']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('UserAssignmentService::create failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data'  => $data,
            ]);
            throw $e;
        }
    }

    public function update(array $data, UserAssignment $assignment): UserAssignment
    {
        DB::beginTransaction();

        try {
            $assignment->user_id                = $data['user_id'] ?? $assignment->user_id;
            $assignment->evaluation_template_id = $data['evaluation_template_id'] ?? $assignment->evaluation_template_id;
            $assignment->due_date               = $data['due_date'] ?? $assignment->due_date;
            $assignment->status                 = $data['status'] ?? $assignment->status;
            $assignment->notes                  = $data['notes'] ?? $assignment->notes;
            $assignment->save();

            DB::commit();
            return $assignment->load(['user', 'template', 'assigner']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('UserAssignmentService::update failed: ' . $e->getMessage(), [
                'trace'         => $e->getTraceAsString(),
                'data'          => $data,
                'assignment_id' => $assignment->id,
            ]);
            throw $e;
        }
    }

    public function delete(UserAssignment $assignment): void
    {
        try {
            $assignment->delete();
        } catch (Exception $e) {
            Log::error("UserAssignmentService::delete failed for assignment {$assignment->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function getFormOptions(): array
    {
        try {
            return [
                'users'     => User::select('id', 'name', 'email')->get(),
                'templates' => EvaluationTemplate::where('is_active', true)->get(),
                'statuses'  => ['pending', 'in_progress', 'completed'],
            ];
        } catch (Exception $e) {
            Log::error('UserAssignmentService::getFormOptions failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
