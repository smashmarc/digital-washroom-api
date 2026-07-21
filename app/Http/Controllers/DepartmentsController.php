<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\DepartmentService;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\DepartmentResource;
use App\Http\Requests\CreateDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;

class DepartmentsController extends Controller
{
    protected DepartmentService $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    public function options(): JsonResponse
    {
        $items = Department::orderBy('name')->get(['id', 'name']);
        return ApiResponse::success('Department options fetched successfully.', $items);
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('view', new Department());
        $params = $request->only(['search', 'sort_by', 'sort_dir', 'per_page']);
        $items = $this->departmentService->searchPaginatedList($params);
        return ApiResponse::success('Departments fetched successfully.', $items, 200, DepartmentResource::class);
    }

    public function store(CreateDepartmentRequest $request): JsonResponse
    {
        $item = $this->departmentService->create($request->validated());
        return ApiResponse::success('Department created successfully.', new DepartmentResource($item), 201);
    }

    public function show(Department $department): JsonResponse
    {
        Gate::authorize('view', new Department());
        return ApiResponse::success('Department fetched successfully.', new DepartmentResource($department));
    }

    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $item = $this->departmentService->update($request->validated(), $department);
        return ApiResponse::success('Department updated successfully.', new DepartmentResource($item));
    }

    public function destroy(Department $department): JsonResponse
    {
        Gate::authorize('delete', $department);
        $department->delete();
        return ApiResponse::success('Department deleted successfully.', null, 200);
    }
}
