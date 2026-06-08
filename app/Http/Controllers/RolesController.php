<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\AttachePermissionsToRoleRequest;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Resources\RoleFormOptionsResource;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RolesController extends Controller
{
    public function __construct(protected RoleService $roleService) {}

    public function index(Request $request): JsonResponse
    {
        if (!Gate::any('view', new Role())) {
            abort(403);
        }
        $params = $request->only(['search', 'sort_by', 'sort_dir', 'per_page', 'with']);
        $roles = $this->roleService->searchPaginatedList($params);
        return ApiResponse::success('Roles fetched successfully.', $roles, 200, RoleResource::class);
    }

    public function store(CreateRoleRequest $request): JsonResponse
    {
        Gate::authorize('create', new Role());
        $role = $this->roleService->create($request->validated());
        return ApiResponse::success('Role created successfully.', new RoleResource($role), 201);
    }

    public function show(Role $role): JsonResponse
    {
        Gate::authorize('view', new Role());
        $role->load('permissions');
        return ApiResponse::success('Role fetched successfully.', new RoleResource($role));
    }

    public function update(CreateRoleRequest $request, Role $role): JsonResponse
    {
        Gate::authorize('update', $role);
        $role = $this->roleService->update($request->validated(), $role);
        return ApiResponse::success('Role updated successfully.', new RoleResource($role));
    }

    public function destroy(Role $role): JsonResponse
    {
        Gate::authorize('delete', $role);
        $this->roleService->delete($role);
        return ApiResponse::success('Role deleted successfully.');
    }

    public function assignPermissions(AttachePermissionsToRoleRequest $request, Role $role): JsonResponse
    {
        $role = $this->roleService->attachePermissions($role, $request->validated()['permissions']);
        return ApiResponse::success('Permissions assigned successfully.', new RoleResource($role->load('permissions')));
    }

    public function getFormOptions(): JsonResponse
    {
        if (!Gate::any(['create', 'update'], Role::class)) {
            abort(403);
        }
        $formOptions = $this->roleService->getFormOptions();
        return ApiResponse::success('Form options fetched.', new RoleFormOptionsResource($formOptions));
    }
}
