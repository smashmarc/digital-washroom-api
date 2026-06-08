<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateUserAssignmentRequest;
use App\Http\Requests\UpdateUserAssignmentRequest;
use App\Http\Resources\UserAssignmentFormOptionsResource;
use App\Http\Resources\UserAssignmentResource;
use App\Models\UserAssignment;
use App\Services\UserAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserAssignmentController extends Controller
{
    public function __construct(protected UserAssignmentService $userAssignmentService) {}

    public function index(Request $request): JsonResponse
    {
        if (!Gate::any('view', new UserAssignment())) {
            abort(403);
        }
        $params = $request->only(['search', 'sort_by', 'sort_dir', 'per_page', 'with']);
        $assignments = $this->userAssignmentService->searchPaginatedList($params);
        return ApiResponse::success('User assignments fetched successfully.', $assignments, 200, UserAssignmentResource::class);
    }

    public function store(CreateUserAssignmentRequest $request): JsonResponse
    {
        Gate::authorize('create', new UserAssignment());
        $assignment = $this->userAssignmentService->create($request->validated());
        return ApiResponse::success('User assignment created successfully.', new UserAssignmentResource($assignment), 201);
    }

    public function show(UserAssignment $userAssignment): JsonResponse
    {
        Gate::authorize('view', new UserAssignment());
        $userAssignment->load(['user', 'template.questions.category', 'assigner']);
        return ApiResponse::success('User assignment fetched successfully.', new UserAssignmentResource($userAssignment));
    }

    public function update(UpdateUserAssignmentRequest $request, UserAssignment $userAssignment): JsonResponse
    {
        Gate::authorize('update', $userAssignment);
        $assignment = $this->userAssignmentService->update($request->validated(), $userAssignment);
        return ApiResponse::success('User assignment updated successfully.', new UserAssignmentResource($assignment));
    }

    public function destroy(UserAssignment $userAssignment): JsonResponse
    {
        Gate::authorize('delete', $userAssignment);
        $this->userAssignmentService->delete($userAssignment);
        return ApiResponse::success('User assignment deleted successfully.');
    }

    public function getFormOptions(): JsonResponse
    {
        if (!Gate::any(['create', 'update'], UserAssignment::class)) {
            abort(403);
        }
        $formOptions = $this->userAssignmentService->getFormOptions();
        return ApiResponse::success('Form options fetched.', new UserAssignmentFormOptionsResource($formOptions));
    }
}
