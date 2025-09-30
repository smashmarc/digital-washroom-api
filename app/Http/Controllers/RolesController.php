<?php

namespace App\Http\Controllers;

use Exception;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use App\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\RoleResource;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Resources\RoleFormOptionsResource;
use App\Http\Requests\AttachePermissionsToRoleRequest;

class RolesController extends Controller
{
    public function __construct(protected RoleService $roleService) {}

    public function index(Request $request): JsonResponse
    { 
        
        $user = $request->user()->load('roles.permissions');
    //     Log::debug('User accessing Role index', [
    //     'id' => $user->id,
    //     'name' => $user->name,
    //     'roles' => $user->roles->pluck('name'),
    //     'permissions' => $user->roles
    //         ->flatMap(fn ($role) => $role->permissions->pluck('name'))
    //         ->unique()
    //         ->values(),
    // ]);

       if (!Gate::any('view', \App\Models\Role::class)){
            abort(403);
       }
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
            return ApiResponse::success(
                'Roles fetched successfully.',
                $roles,
                200,
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
            return ApiResponse::success('Role created successfully.', new RoleResource($role), 201);
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
        Gate::authorize('view', Role::class);
        $role->load('permissions');
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
        Gate::authorize('delete', Role::class);
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

    public function getFormOptions()
    {
        if(!Gate::any(['create', 'update'], Role::class)) {
            abort(403);
        }
        try {
           
           $formOptions = $this->roleService->getFormOptions();

            return ApiResponse::success(
                'Form options fetched.',
                new RoleFormOptionsResource($formOptions)
            );
        } catch (\Exception $e) {
             Log::error(__METHOD__ . $e->getMessage(), [               
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Failed to fetch Form Options.', 500);
        }
    }
}
