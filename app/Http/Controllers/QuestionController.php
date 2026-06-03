<?php

namespace App\Http\Controllers;

use Exception;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Models\Question;
use App\Services\QuestionService;
use App\Http\Resources\QuestionResource;
use App\Http\Resources\QuestionFormOptionsResource;
use App\Http\Requests\CreateQuestionRequest;

class QuestionController extends Controller
{
    public function __construct(protected QuestionService $questionService) {}

    public function index(Request $request): JsonResponse
    {
        if (!Gate::any('view', new Question())) {
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
            $questions = $this->questionService->searchPaginatedList($params);
            return ApiResponse::success(
                'Questions fetched successfully.',
                $questions,
                200,
                QuestionResource::class
            );
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch questions. ' . $e->getMessage(), 500);
        }
    }

    public function store(CreateQuestionRequest $request): JsonResponse
    {
        Gate::authorize('create', new Question());

        try {
            $question = $this->questionService->create($request->validated());
            return ApiResponse::success('Question created successfully.', new QuestionResource($question), 201);
        } catch (Exception $e) {
            return ApiResponse::error('Failed to create question. ' . $e->getMessage(), 500);
        }
    }

    public function show(Question $question): JsonResponse
    {
        Gate::authorize('view', new Question());

        try {
            $question->load('category');
            return ApiResponse::success('Question fetched successfully.', new QuestionResource($question));
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch question. ' . $e->getMessage(), 500);
        }
    }

    public function update(CreateQuestionRequest $request, Question $question): JsonResponse
    {
        Gate::authorize('update', $question);

        try {
            $question = $this->questionService->update($request->validated(), $question);
            return ApiResponse::success('Question updated successfully.', new QuestionResource($question));
        } catch (Exception $e) {
            return ApiResponse::error('Failed to update question. ' . $e->getMessage(), 500);
        }
    }

    public function destroy(Question $question): JsonResponse
    {
        Gate::authorize('delete', $question);

        try {
            $this->questionService->delete($question);
            return ApiResponse::success('Question deleted successfully.');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to delete question. ' . $e->getMessage(), 500);
        }
    }

    public function getFormOptions(): JsonResponse
    {
        if (!Gate::any(['create', 'update'], Question::class)) {
            abort(403);
        }

        try {
            $formOptions = $this->questionService->getFormOptions();
            return ApiResponse::success('Form options fetched.', new QuestionFormOptionsResource($formOptions));
        } catch (Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to fetch form options. ' . $e->getMessage(), 500);
        }
    }
}
