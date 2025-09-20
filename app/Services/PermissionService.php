<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PermissionService

{
    public function list(int $perPage = 15, ?string $search = null)
    {
        $query = Permission::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $paginator = $query->paginate($perPage);

        return $paginator;
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
