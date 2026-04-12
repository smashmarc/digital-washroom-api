<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PermissionService extends BaseService

{

    public function __construct(Permission $model)
    {
        parent::__construct($model);
    }

    /**
     * List roles with custom searchable columns.
     */
    public function searchPaginatedList(array $params = [])
    {
        $params['searchableColumns'] =['name', 'description'];
        return parent::list($params);
    }
    

    

    public function create(array $data)
    {
        $permission = Permission::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'api',
        ]);

        return $permission;
    }

    public function find(int $id)
    {
        return Permission::find($id);
    }

    public function update(int $id, array $data)
    {
        $permission = $this->find($id);

        if (!$permission) return null;

        $permission->update([
            'name' => $data['name'] ?? $permission->name,
            'guard_name' => $data['guard_name'] ?? $permission->guard_name,
        ]);

        return $permission;
    }

    public function delete(int $id): bool
    {
        $permission = $this->find($id);

        if (!$permission) return false;

        return $permission->delete();
    }
}
