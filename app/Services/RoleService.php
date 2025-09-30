<?php

namespace App\Services;

use Exception;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class RoleService extends BaseService
{
    public function __construct(Role $role)
    {
        parent::__construct($role);
    }

    /**
     * List roles with custom searchable columns.
     */
    public function searchPaginatedList(array $params = [], $columns=[])
    {
        return parent::list($params, $columns);
    }
    

    public function create(array $data)
    {
        DB::beginTransaction();

        try {
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => 'api',
                'description' => $data['description'] ?? null,
            ]);

            // Assign permissions if provided
            if (!empty($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            DB::commit();
            return $role;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RoleService create failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    public function update(array $data, Role $role)
    {
        DB::beginTransaction();

        try {
            $role->name = $data['name'];
            $role->description = $data['description'] ?? $role->description;
            $role->save();

            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            DB::commit();
            return $role;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RoleService update failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $data,
                'role_id' => $role->id,
            ]);
            throw $e;
        }
    }

    public function delete(Role $role): void
    {
        try {
            $role->delete();
        } catch (Exception $e) {
            Log::error("Failed to delete role {$role->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function attachePermissions(Role $role, array $permissions): Role
    {
        try {
            $role->syncPermissions($permissions);
            return $role;
        } catch (Exception $e) {
            // Log the error for debugging
            \Log::error('Failed to assign permissions', [
                'role_id' => $role->id,
                'permissions' => $permissions,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Error assigning permissions to role.');
        }
    }

    public function getFormOptions()
    {        
         try {
           $permissions= Permission::all();          
           return ['permissions'=>$permissions];        
        } catch (Exception $e) {               
            Log::error('Failed to fetch form options ' . $e->getMessage(), [               
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
