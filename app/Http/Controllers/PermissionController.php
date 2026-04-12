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
use App\Http\Requests\PermissionRequest;
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
        $permission = Permission::create($request->validated());

        return ApiResponse::success(
            'Permission created successfully.',
            new PermissionResource($permission),
            201
        );
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
    public function update(Request $request, Permission $permission): JsonResponse
    {
        $permission->update($request->validated());

        return ApiResponse::success(
            'Permission updated successfully.',
            new PermissionResource($permission)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission): JsonResponse
    {
        $permission->delete();

        return ApiResponse::success('Permission deleted successfully.');
    }
}
