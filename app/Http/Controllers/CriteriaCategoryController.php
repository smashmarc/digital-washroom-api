<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateCriteriaCategoryRequest;
use App\Http\Resources\CriteriaCategoryFormOptionsResource;
use App\Http\Resources\CriteriaCategoryResource;
use App\Models\CriteriaCategory;
use App\Services\CriteriaCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CriteriaCategoryController extends Controller
{
    public function __construct(protected CriteriaCategoryService $criteriaCategoryService) {}

    public function index(Request $request): JsonResponse
    {
        if (!Gate::any('view', new CriteriaCategory())) {
            abort(403);
        }
        $params = $request->only(['search', 'sort_by', 'sort_dir', 'per_page', 'with']);
        $categories = $this->criteriaCategoryService->searchPaginatedList($params);
        return ApiResponse::success('Criteria categories fetched successfully.', $categories, 200, CriteriaCategoryResource::class);
    }

    public function store(CreateCriteriaCategoryRequest $request): JsonResponse
    {
        Gate::authorize('create', new CriteriaCategory());
        $category = $this->criteriaCategoryService->create($request->validated());
        return ApiResponse::success('Criteria category created successfully.', new CriteriaCategoryResource($category), 201);
    }

    public function show(CriteriaCategory $criteriaCategory): JsonResponse
    {
        Gate::authorize('view', new CriteriaCategory());
        $criteriaCategory->load('criteria');
        return ApiResponse::success('Criteria category fetched successfully.', new CriteriaCategoryResource($criteriaCategory));
    }

    public function update(CreateCriteriaCategoryRequest $request, CriteriaCategory $criteriaCategory): JsonResponse
    {
        Gate::authorize('update', $criteriaCategory);
        $category = $this->criteriaCategoryService->update($request->validated(), $criteriaCategory);
        return ApiResponse::success('Criteria category updated successfully.', new CriteriaCategoryResource($category));
    }

    public function destroy(CriteriaCategory $criteriaCategory): JsonResponse
    {
        Gate::authorize('delete', $criteriaCategory);
        $this->criteriaCategoryService->delete($criteriaCategory);
        return ApiResponse::success('Criteria category deleted successfully.');
    }

    public function getFormOptions(): JsonResponse
    {
        if (!Gate::any(['create', 'update'], CriteriaCategory::class)) {
            abort(403);
        }
        $formOptions = $this->criteriaCategoryService->getFormOptions();
        return ApiResponse::success('Form options fetched.', new CriteriaCategoryFormOptionsResource($formOptions));
    }
}
