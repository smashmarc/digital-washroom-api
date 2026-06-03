<?php

namespace App\Http\Controllers;

use Exception;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Models\UserAssignment;
use App\Services\UserAssignmentService;
use App\Http\Resources\UserAssignmentResource;
use App\Http\Resources\UserAssignmentFormOptionsResource;
use App\Http\Requests\CreateUserAssignmentRequest;
use App\Http\Requests\UpdateUserAssignmentRequest;

class UserAssignmentController extends Controller
{
    public function __construct(protected UserAssignmentService $userAssignmentService) {}

    public function index(Request $request): JsonResponse
    {
        if (!Gate::any('view', new UserAssignment())) {
            abort(403);
        }

        $params = $request->only([
            'search',
            'sort_by',
            'sort_dir',
            'per_page',
            'with',
        ]);

        try {
            $assignments = $this->userAssignmentService->searchPaginatedList($params);
            return ApiResponse::success(
                'User assignments fetched successfully.',
                $assignments,
                200,
                UserAssignmentResource::class
            );
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch user assignments. ' . $e->getMessage(), 500);
        }
    }

    public function store(CreateUserAssignmentRequest $request): JsonResponse
    {
        Gate::authorize('create', new UserAssignment());

        try {
            $assignment = $this->userAssignmentService->create($request->validated());
            return ApiResponse::success('User assignment created successfully.', new UserAssignmentResource($assignment), 201);
        } catch (Exception $e) {
            return ApiResponse::error('Failed to create user assignment. ' . $e->getMessage(), 500);
        }
    }

    public function show(UserAssignment $userAssignment): JsonResponse
    {
        Gate::authorize('view', new UserAssignment());

        try {
            $userAssignment->load(['user', 'template.questions.category', 'assigner']);
            return ApiResponse::success('User assignment fetched successfully.', new UserAssignmentResource($userAssignment));
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch user assignment. ' . $e->getMessage(), 500);
        }
    }

    public function update(UpdateUserAssignmentRequest $request, UserAssignment $userAssignment): JsonResponse
    {
        Gate::authorize('update', $userAssignment);

        try {
            $assignment = $this->userAssignmentService->update($request->validated(), $userAssignment);
            return ApiResponse::success('User assignment updated successfully.', new UserAssignmentResource($assignment));
        } catch (Exception $e) {
            return ApiResponse::error('Failed to update user assignment. ' . $e->getMessage(), 500);
        }
    }

    public function destroy(UserAssignment $userAssignment): JsonResponse
    {
        Gate::authorize('delete', $userAssignment);

        try {
            $this->userAssignmentService->delete($userAssignment);
            return ApiResponse::success('User assignment deleted successfully.');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to delete user assignment. ' . $e->getMessage(), 500);
        }
    }

    public function getFormOptions(): JsonResponse
    {
        if (!Gate::any(['create', 'update'], UserAssignment::class)) {
            abort(403);
        }

        try {
            $formOptions = $this->userAssignmentService->getFormOptions();
            return ApiResponse::success('Form options fetched.', new UserAssignmentFormOptionsResource($formOptions));
        } catch (Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to fetch form options. ' . $e->getMessage(), 500);
        }
    }
}
