<?php

namespace App\Services;

use App\Models\Role;
use Exception;
use App\Models\Log as LogModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogService extends BaseService
{
    public function __construct(LogModel $model)
    {
        parent::__construct($model);
    }

    /**
     * List roles with custom searchable columns.
     */
    public function searchPaginatedList(array $params = [], $columns = [])
    {
        return parent::list($params, $columns);
    }


    public function create(array $data): LogModel
    {     
        try {
            return LogModel::create($data);        
        } catch (Exception $e) {          
            Log::error('Failed to create role: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function update(array $data, \App\Models\User $user)
    {
        DB::beginTransaction();

        try {
            // Update user fields          
            $user->update($data);
            if (isset($data['roles'])) {
                 $user->syncRoles($data['roles']);
            }
                       
            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('UserService update failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $data,
                'user_id' => $user->id,
            ]);
            throw $e; // controller will handle ApiResponse
        }
    }

    // public function delete(Role $role): void
    // {
    //     try {
    //         $role->delete();
    //     } catch (Exception $e) {
    //         Log::error("Failed to delete role {$role->id}: " . $e->getMessage(), [
    //             'trace' => $e->getTraceAsString(),
    //         ]);
    //         throw $e;
    //     }
    // }
}
