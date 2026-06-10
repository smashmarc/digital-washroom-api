<?php

namespace App\Http\Controllers;

use Exception;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Models\QuestionCategory;
use App\Services\QuestionCategoryService;
use App\Http\Resources\QuestionCategoryResource;
use App\Http\Resources\QuestionCategoryFormOptionsResource;
use App\Http\Requests\CreateQuestionCategoryRequest;

class QuestionCategoryController extends Controller
{
    public function __construct(protected QuestionCategoryService $questionCategoryService) {}

    public function index(Request $request): JsonResponse
    {
        if (!Gate::any('view', new QuestionCategory())) {
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
            $categories = $this->questionCategoryService->searchPaginatedList($params);
            return ApiResponse::success(
                'Question categories fetched successfully.',
                $categories,
                200,
                QuestionCategoryResource::class
            );
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch question categories. ' . $e->getMessage(), 500);
        }
    }

    public function store(CreateQuestionCategoryRequest $request): JsonResponse
    {
        Gate::authorize('create', new QuestionCategory());

        try {
            $category = $this->questionCategoryService->create($request->validated());
            return ApiResponse::success('Question category created successfully.', new QuestionCategoryResource($category), 201);
        } catch (Exception $e) {
            return ApiResponse::error('Failed to create question category. ' . $e->getMessage(), 500);
        }
    }

    public function show(QuestionCategory $questionCategory): JsonResponse
    {
        Gate::authorize('view', new QuestionCategory());

        try {
            $questionCategory->load('questions');
            return ApiResponse::success('Question category fetched successfully.', new QuestionCategoryResource($questionCategory));
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch question category. ' . $e->getMessage(), 500);
        }
    }

    public function update(CreateQuestionCategoryRequest $request, QuestionCategory $questionCategory): JsonResponse
    {
        Gate::authorize('update', $questionCategory);

        try {
            $category = $this->questionCategoryService->update($request->validated(), $questionCategory);
            return ApiResponse::success('Question category updated successfully.', new QuestionCategoryResource($category));
        } catch (Exception $e) {
            return ApiResponse::error('Failed to update question category. ' . $e->getMessage(), 500);
        }
    }

    public function destroy(QuestionCategory $questionCategory): JsonResponse
    {
        Gate::authorize('delete', $questionCategory);

        try {
            $this->questionCategoryService->delete($questionCategory);
            return ApiResponse::success('Question category deleted successfully.');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to delete question category. ' . $e->getMessage(), 500);
        }
    }

    public function getFormOptions(): JsonResponse
    {
        if (!Gate::any(['create', 'update'], QuestionCategory::class)) {
            abort(403);
        }

        try {
            $formOptions = $this->questionCategoryService->getFormOptions();
            return ApiResponse::success('Form options fetched.', new QuestionCategoryFormOptionsResource($formOptions));
        } catch (Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to fetch form options. ' . $e->getMessage(), 500);
        }
    }
}