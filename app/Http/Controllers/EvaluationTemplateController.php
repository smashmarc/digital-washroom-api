<?php

namespace App\Http\Controllers;

use Exception;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Models\Question;
use App\Models\EvaluationTemplate;
use App\Services\EvaluationTemplateService;
use App\Http\Resources\EvaluationTemplateResource;
use App\Http\Resources\EvaluationTemplateFormOptionsResource;
use App\Http\Requests\CreateEvaluationTemplateRequest;
use App\Http\Requests\AttachQuestionsToTemplateRequest;

class EvaluationTemplateController extends Controller
{
    public function __construct(protected EvaluationTemplateService $evaluationTemplateService) {}

    public function index(Request $request): JsonResponse
    {
        if (!Gate::any('view', new EvaluationTemplate())) {
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
            $templates = $this->evaluationTemplateService->searchPaginatedList($params);
            return ApiResponse::success(
                'Evaluation templates fetched successfully.',
                $templates,
                200,
                EvaluationTemplateResource::class
            );
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch evaluation templates. ' . $e->getMessage(), 500);
        }
    }

    public function store(CreateEvaluationTemplateRequest $request): JsonResponse
    {
        Gate::authorize('create', new EvaluationTemplate());

        try {
            $template = $this->evaluationTemplateService->create($request->validated());
            return ApiResponse::success('Evaluation template created successfully.', new EvaluationTemplateResource($template), 201);
        } catch (Exception $e) {
            return ApiResponse::error('Failed to create evaluation template. ' . $e->getMessage(), 500);
        }
    }

    public function show(EvaluationTemplate $evaluationTemplate): JsonResponse
    {
        Gate::authorize('view', new EvaluationTemplate());

        try {
            $evaluationTemplate->load('questions.category', 'creator');
            return ApiResponse::success('Evaluation template fetched successfully.', new EvaluationTemplateResource($evaluationTemplate));
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch evaluation template. ' . $e->getMessage(), 500);
        }
    }

    public function update(CreateEvaluationTemplateRequest $request, EvaluationTemplate $evaluationTemplate): JsonResponse
    {
        Gate::authorize('update', $evaluationTemplate);

        try {
            $template = $this->evaluationTemplateService->update($request->validated(), $evaluationTemplate);
            return ApiResponse::success('Evaluation template updated successfully.', new EvaluationTemplateResource($template));
        } catch (Exception $e) {
            return ApiResponse::error('Failed to update evaluation template. ' . $e->getMessage(), 500);
        }
    }

    public function destroy(EvaluationTemplate $evaluationTemplate): JsonResponse
    {
        Gate::authorize('delete', $evaluationTemplate);

        try {
            $this->evaluationTemplateService->delete($evaluationTemplate);
            return ApiResponse::success('Evaluation template deleted successfully.');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to delete evaluation template. ' . $e->getMessage(), 500);
        }
    }

    /**
     * Attach one or many questions to the template.
     * POST /api/evaluation-templates/{evaluationTemplate}/questions
     */
    public function attachQuestions(AttachQuestionsToTemplateRequest $request, EvaluationTemplate $evaluationTemplate): JsonResponse
    {
        Gate::authorize('update', $evaluationTemplate);

        try {
            $template = $this->evaluationTemplateService->attachQuestions(
                $evaluationTemplate,
                $request->validated()['questions']
            );
            return ApiResponse::success(
                'Questions attached to template successfully.',
                new EvaluationTemplateResource($template->load('questions.category'))
            );
        } catch (Exception $e) {
            return ApiResponse::error('Failed to attach questions. ' . $e->getMessage(), 500);
        }
    }

    /**
     * Detach a single question from the template.
     * DELETE /api/evaluation-templates/{evaluationTemplate}/questions/{question}
     */
    public function detachQuestion(EvaluationTemplate $evaluationTemplate, Question $question): JsonResponse
    {
        Gate::authorize('update', $evaluationTemplate);

        try {
            $template = $this->evaluationTemplateService->detachQuestion($evaluationTemplate, $question);
            return ApiResponse::success(
                'Question detached from template successfully.',
                new EvaluationTemplateResource($template->load('questions.category'))
            );
        } catch (Exception $e) {
            return ApiResponse::error('Failed to detach question. ' . $e->getMessage(), 500);
        }
    }

    public function getFormOptions(): JsonResponse
    {
        if (!Gate::any(['create', 'update'], EvaluationTemplate::class)) {
            abort(403);
        }

        try {
            $formOptions = $this->evaluationTemplateService->getFormOptions();
            return ApiResponse::success('Form options fetched.', new EvaluationTemplateFormOptionsResource($formOptions));
        } catch (Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to fetch form options. ' . $e->getMessage(), 500);
        }
    }
}