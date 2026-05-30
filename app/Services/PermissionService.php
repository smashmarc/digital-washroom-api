<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;

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
            'name'        => $data['name'],
            'guard_name'  => $data['guard_name'] ?? 'api',
            'description' => $data['description'] ?? null,
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
            'name'        => $data['name'] ?? $permission->name,
            'guard_name'  => $data['guard_name'] ?? $permission->guard_name,
            'description' => array_key_exists('description', $data) ? $data['description'] : $permission->description,
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
