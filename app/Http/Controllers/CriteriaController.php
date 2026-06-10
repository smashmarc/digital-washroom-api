<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateCriteriaRequest;
use App\Http\Resources\CriteriaFormOptionsResource;
use App\Http\Resources\CriteriaResource;
use App\Models\Criteria;
use App\Services\CriteriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CriteriaController extends Controller
{
    public function __construct(protected CriteriaService $criteriaService) {}

    public function index(Request $request): JsonResponse
    {
        if (!Gate::any('view', new Criteria())) {
            abort(403);
        }
        $params = $request->only(['search', 'sort_by', 'sort_dir', 'per_page', 'with']);
        $criteria = $this->criteriaService->searchPaginatedList($params);
        return ApiResponse::success('Criteria fetched successfully.', $criteria, 200, CriteriaResource::class);
    }

    public function store(CreateCriteriaRequest $request): JsonResponse
    {
        Gate::authorize('create', new Criteria());
        $criteria = $this->criteriaService->create($request->validated());
        return ApiResponse::success('Criteria created successfully.', new CriteriaResource($criteria), 201);
    }

    public function show(Criteria $criteria): JsonResponse
    {
        Gate::authorize('view', new Criteria());
        $criteria->load(['category', 'evaluationTemplates']);
        return ApiResponse::success('Criteria fetched successfully.', new CriteriaResource($criteria));
    }

    public function update(CreateCriteriaRequest $request, Criteria $criteria): JsonResponse
    {
        Gate::authorize('update', $criteria);
        $criteria = $this->criteriaService->update($request->validated(), $criteria);
        return ApiResponse::success('Criteria updated successfully.', new CriteriaResource($criteria));
    }

    public function destroy(Criteria $criteria): JsonResponse
    {
        Gate::authorize('delete', $criteria);
        $this->criteriaService->delete($criteria);
        return ApiResponse::success('Criteria deleted successfully.');
    }

    public function getFormOptions(): JsonResponse
    {
        if (!Gate::any(['create', 'update'], Criteria::class)) {
            abort(403);
        }
        $formOptions = $this->criteriaService->getFormOptions();
        return ApiResponse::success('Form options fetched.', new CriteriaFormOptionsResource($formOptions));
    }
}
