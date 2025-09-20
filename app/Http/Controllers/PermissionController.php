<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionRequest;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Http\Requests\CreatePermissionRequest;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $permissions = Permission::latest()->paginate($request->get('per_page', 10));

        return ApiResponse::paginated(
            'Permissions retrieved successfully.',
            $permissions,
            PermissionResource::class
        );
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
    public function update(PermissionRequest $request, Permission $permission): JsonResponse
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
