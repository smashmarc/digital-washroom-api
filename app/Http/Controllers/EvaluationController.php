<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateEvaluationRequest;
use App\Http\Requests\SaveEvaluationAnswersRequest;
use App\Http\Requests\UpdateEvaluationAnswerRequest;
use App\Http\Requests\UpdateEvaluationRequest;
use App\Http\Resources\EvaluationAnswerResource;
use App\Http\Resources\EvaluationFormOptionsResource;
use App\Http\Resources\EvaluationResource;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Services\EvaluationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EvaluationController extends Controller
{
    public function __construct(protected EvaluationService $evaluationService) {}

    public function index(Request $request): JsonResponse
    {
        if (!Gate::any('view', new Evaluation())) {
            abort(403);
        }
        $params = $request->only(['search', 'sort_by', 'sort_dir', 'per_page', 'with', 'user_id', 'date_from', 'date_to']);
        $evaluations = $this->evaluationService->searchPaginatedList($params);
        return ApiResponse::success('Evaluations fetched successfully.', $evaluations, 200, EvaluationResource::class);
    }

    public function store(CreateEvaluationRequest $request): JsonResponse
    {
        Gate::authorize('create', new Evaluation());
        $evaluation = $this->evaluationService->create($request->validated());
        return ApiResponse::success('Evaluation created successfully.', new EvaluationResource($evaluation), 201);
    }

    public function show(Evaluation $evaluation): JsonResponse
    {
        Gate::authorize('view', new Evaluation());
        $evaluation->load(['user', 'template.criteria.category', 'evaluator', 'evaluationDepartments', 'answers.criteria.category']);
        return ApiResponse::success('Evaluation fetched successfully.', new EvaluationResource($evaluation));
    }

    public function update(UpdateEvaluationRequest $request, Evaluation $evaluation): JsonResponse
    {
        Gate::authorize('update', $evaluation);
        $evaluation = $this->evaluationService->update($request->validated(), $evaluation);
        return ApiResponse::success('Evaluation updated successfully.', new EvaluationResource($evaluation));
    }

    public function saveAnswers(SaveEvaluationAnswersRequest $request, Evaluation $evaluation): JsonResponse
    {
        Gate::authorize('update', $evaluation);
        $evaluation = $this->evaluationService->saveAnswers($evaluation, $request->validated()['answers']);
        return ApiResponse::success('Answers saved successfully.', new EvaluationResource($evaluation->load(['answers.criteria', 'template'])));
    }

    public function updateAnswer(UpdateEvaluationAnswerRequest $request, EvaluationAnswer $answer): JsonResponse
    {
        Gate::authorize('update', $answer->evaluation);
        $answer = $this->evaluationService->updateAnswer($request->validated(), $answer);
        return ApiResponse::success('Answer updated successfully.', new EvaluationAnswerResource($answer));
    }

    public function submit(Evaluation $evaluation): JsonResponse
    {
        Gate::authorize('update', $evaluation);
        $evaluation = $this->evaluationService->submit($evaluation);
        return ApiResponse::success('Evaluation submitted successfully.', new EvaluationResource($evaluation));
    }

    public function getFormOptions(): JsonResponse
    {
        if (!Gate::any(['create', 'update'], Evaluation::class)) {
            abort(403);
        }
        $formOptions = $this->evaluationService->getFormOptions();
        return ApiResponse::success('Form options fetched.', new EvaluationFormOptionsResource($formOptions));
    }
}
