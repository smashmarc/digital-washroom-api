<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Permission;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\PermissionService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\PermissionResource;
use App\Http\Requests\CreatePermissionRequest;

class PermissionController extends Controller
{

    protected $permissionService;
    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {

        Gate::authorize('manage', Permission::class);
        $params = $request->only([
            'search',
            'sort_by',
            'sort_dir',
            'per_page',
            'with'
        ]);
       
        try {
            $roles = $this->permissionService->searchPaginatedList($params);
            return ApiResponse::success(
                'Roles fetched successfully.',
                $roles,
                200,
                PermissionResource::class
            );
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch roles.', 500);
        }
    }

    /**
     * Store a newly created resource.
     */
    public function store(CreatePermissionRequest $request): JsonResponse
    {
        Gate::authorize('manage', Permission::class);
        try {
            $permission = $this->permissionService->create($request->validated());
            return ApiResponse::success('Permission created successfully.', new PermissionResource($permission), 201);
        } catch (Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to create permission.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission): JsonResponse
    {
        return ApiResponse::success(
            'Permission retrieved successfully.',
            new PermissionResource($permission)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CreatePermissionRequest $request, Permission $permission): JsonResponse
    {
        Gate::authorize('manage', Permission::class);
        try {
            $this->permissionService->update($permission->id, $request->validated());
            return ApiResponse::success('Permission updated successfully.', new PermissionResource($permission->fresh()));
        } catch (Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to update permission.', 500);
        }
    }

    public function destroy(Permission $permission): JsonResponse
    {
        Gate::authorize('manage', Permission::class);
        try {
            $this->permissionService->delete($permission->id);
            return ApiResponse::success('Permission deleted successfully.');
        } catch (Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to delete permission.', 500);
        }
    }
}
