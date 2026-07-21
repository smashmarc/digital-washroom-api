<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PermissionController extends Controller
{
    public function __construct(protected PermissionService $permissionService) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('manage', Permission::class);
        $params = $request->only(['search', 'sort_by', 'sort_dir', 'per_page', 'with']);
        $permissions = $this->permissionService->searchPaginatedList($params);
        return ApiResponse::success('Permissions fetched successfully.', $permissions, 200, PermissionResource::class);
    }

    public function store(CreatePermissionRequest $request): JsonResponse
    {
        Gate::authorize('manage', Permission::class);
        $permission = $this->permissionService->create($request->validated());
        return ApiResponse::success('Permission created successfully.', new PermissionResource($permission), 201);
    }

    public function show(Permission $permission): JsonResponse
    {
        return ApiResponse::success('Permission retrieved successfully.', new PermissionResource($permission));
    }

    public function update(CreatePermissionRequest $request, Permission $permission): JsonResponse
    {
        Gate::authorize('manage', Permission::class);
        $this->permissionService->update($permission->id, $request->validated());
        return ApiResponse::success('Permission updated successfully.', new PermissionResource($permission->fresh()));
    }

    public function destroy(Permission $permission): JsonResponse
    {
        Gate::authorize('manage', Permission::class);
        $this->permissionService->delete($permission->id);
        return ApiResponse::success('Permission deleted successfully.');
    }
}
