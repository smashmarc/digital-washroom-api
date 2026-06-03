<?php

namespace App\Http\Controllers;

use Exception;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Services\EvaluationService;
use App\Http\Resources\EvaluationResource;
use App\Http\Resources\EvaluationAnswerResource;
use App\Http\Resources\EvaluationFormOptionsResource;
use App\Http\Requests\CreateEvaluationRequest;
use App\Http\Requests\UpdateEvaluationRequest;
use App\Http\Requests\SaveEvaluationAnswersRequest;
use App\Http\Requests\UpdateEvaluationAnswerRequest;

class EvaluationController extends Controller
{
    public function __construct(protected EvaluationService $evaluationService) {}

    public function index(Request $request): JsonResponse
    {
        if (!Gate::any('view', new Evaluation())) {
            abort(403);
        }

        $params = $request->only([
            'search',
            'sort_by',
            'sort_dir',
            'per_page',
            'with',
            'user_id',
            'date_from',
            'date_to',
        ]);

        try {
            $evaluations = $this->evaluationService->searchPaginatedList($params);
            return ApiResponse::success(
                'Evaluations fetched successfully.',
                $evaluations,
                200,
                EvaluationResource::class
            );
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch evaluations. ' . $e->getMessage(), 500);
        }
    }

    public function store(CreateEvaluationRequest $request): JsonResponse
    {
        Gate::authorize('create', new Evaluation());

        try {
            $evaluation = $this->evaluationService->create($request->validated());
            return ApiResponse::success('Evaluation created successfully.', new EvaluationResource($evaluation), 201);
        } catch (Exception $e) {
            return ApiResponse::error('Failed to create evaluation. ' . $e->getMessage(), 500);
        }
    }

    public function show(Evaluation $evaluation): JsonResponse
    {
        Gate::authorize('view', new Evaluation());

        try {
            $evaluation->load(['assignment.user', 'assignment.template', 'evaluator', 'answers.question.category']);
            return ApiResponse::success('Evaluation fetched successfully.', new EvaluationResource($evaluation));
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch evaluation. ' . $e->getMessage(), 500);
        }
    }

    public function update(UpdateEvaluationRequest $request, Evaluation $evaluation): JsonResponse
    {
        Gate::authorize('update', $evaluation);

        try {
            $evaluation = $this->evaluationService->update($request->validated(), $evaluation);
            return ApiResponse::success('Evaluation updated successfully.', new EvaluationResource($evaluation));
        } catch (Exception $e) {
            return ApiResponse::error('Failed to update evaluation. ' . $e->getMessage(), 500);
        }
    }

    /**
     * Save / upsert answers for an evaluation and recalculate score.
     * POST /api/evaluations/{evaluation}/answers
     */
    public function saveAnswers(SaveEvaluationAnswersRequest $request, Evaluation $evaluation): JsonResponse
    {
        Gate::authorize('update', $evaluation);

        try {
            $evaluation = $this->evaluationService->saveAnswers($evaluation, $request->validated()['answers']);
            return ApiResponse::success(
                'Answers saved successfully.',
                new EvaluationResource($evaluation->load(['answers.question', 'assignment.template']))
            );
        } catch (Exception $e) {
            return ApiResponse::error('Failed to save answers. ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update a single answer by its ID and recalculate score.
     * PUT /api/answers/{answer}
     */
    public function updateAnswer(UpdateEvaluationAnswerRequest $request, EvaluationAnswer $answer): JsonResponse
    {
        Gate::authorize('update', $answer->evaluation);

        try {
            $answer = $this->evaluationService->updateAnswer($request->validated(), $answer);
            return ApiResponse::success('Answer updated successfully.', new EvaluationAnswerResource($answer));
        } catch (Exception $e) {
            return ApiResponse::error('Failed to update answer. ' . $e->getMessage(), 500);
        }
    }

    /**
     * Submit the evaluation — locks it and finalises the result.
     * POST /api/evaluations/{evaluation}/submit
     */
    public function submit(Evaluation $evaluation): JsonResponse
    {
        Gate::authorize('update', $evaluation);

        try {
            $evaluation = $this->evaluationService->submit($evaluation);
            return ApiResponse::success(
                'Evaluation submitted successfully.',
                new EvaluationResource($evaluation)
            );
        } catch (Exception $e) {
            return ApiResponse::error('Failed to submit evaluation. ' . $e->getMessage(), 500);
        }
    }

    public function getFormOptions(): JsonResponse
    {
        if (!Gate::any(['create', 'update'], Evaluation::class)) {
            abort(403);
        }

        try {
            $formOptions = $this->evaluationService->getFormOptions();
            return ApiResponse::success('Form options fetched.', new EvaluationFormOptionsResource($formOptions));
        } catch (Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to fetch form options. ' . $e->getMessage(), 500);
        }
    }
}
