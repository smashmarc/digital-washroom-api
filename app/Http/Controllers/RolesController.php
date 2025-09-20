<?php

namespace App\Http\Controllers;

use Exception;
use App\Helpers\ApiResponse;
use App\Http\Requests\AttachePermissionsToRoleRequest;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use App\Http\Requests\CreateRoleRequest;

class RolesController extends Controller
{
    public function __construct(protected RoleService $roleService) {}

    public function index(Request $request): JsonResponse
    {

        $params = $request->only([
            'search',
            'sort_by',
            'sort_dir',
            'per_page',
            'with'
        ]);
        $columns = ['name'];
        try {
            $roles = $this->roleService->searchPaginatedList($params, $columns);
            return ApiResponse::paginated(
                'Roles fetched successfully.',
                $roles,
                RoleResource::class
            );
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch roles.', 500);
        }
    }

    public function store(CreateRoleRequest $request): JsonResponse
    {
        try {
            $role = $this->roleService->create($request->validated());
            return ApiResponse::success('Role created successfully.', new RoleResource($role));
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to create role.', 500);
        }
    }

    public function update(CreateRoleRequest $request, \App\Models\Role $role): JsonResponse
    {
        try {
            $role = $this->roleService->update($request->validated(), $role);
            return ApiResponse::success('Role updated successfully.', new RoleResource($role));
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update role.', 500);
        }
    }

    public function show(Role $role): JsonResponse
    {
        try {
            return ApiResponse::success(
                'Role fetched successfully.',
                new RoleResource($role)
            );
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch role.', 500);
        }
    }



    public function destroy(Role $role): JsonResponse
    {
        try {
            $this->roleService->delete($role);
            return ApiResponse::success('Role deleted successfully.');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to delete role.', 500);
        }
    }

    public function assignPermissions(AttachePermissionsToRoleRequest $request, Role $role): JsonResponse
    {
        try {
            $role = $this->roleService->attachePermissions(
                $role,
                $request->validated()['permissions']
            );

            return ApiResponse::success(
                'Permissions assigned successfully.',
                new RoleResource($role->load('permissions'))
            );
        } catch (Exception $e) {
            return ApiResponse::error(
                $e->getMessage() ?: 'Failed to assign permissions.',
                500
            );
        }
    }
}
