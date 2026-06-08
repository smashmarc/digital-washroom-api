<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\AttachCriteriaToTemplateRequest;
use App\Http\Requests\CreateEvaluationTemplateRequest;
use App\Http\Resources\EvaluationTemplateFormOptionsResource;
use App\Http\Resources\EvaluationTemplateResource;
use App\Models\Criteria;
use App\Models\EvaluationTemplate;
use App\Services\EvaluationTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EvaluationTemplateController extends Controller
{
    public function __construct(protected EvaluationTemplateService $evaluationTemplateService) {}

    public function index(Request $request): JsonResponse
    {
        if (!Gate::any('view', new EvaluationTemplate())) {
            abort(403);
        }
        $params = $request->only(['search', 'sort_by', 'sort_dir', 'per_page', 'with']);
        $templates = $this->evaluationTemplateService->searchPaginatedList($params);
        return ApiResponse::success('Evaluation templates fetched successfully.', $templates, 200, EvaluationTemplateResource::class);
    }

    public function store(CreateEvaluationTemplateRequest $request): JsonResponse
    {
        Gate::authorize('create', new EvaluationTemplate());
        $template = $this->evaluationTemplateService->create($request->validated());
        return ApiResponse::success('Evaluation template created successfully.', new EvaluationTemplateResource($template), 201);
    }

    public function show(EvaluationTemplate $evaluationTemplate): JsonResponse
    {
        Gate::authorize('view', new EvaluationTemplate());
        $evaluationTemplate->load('criteria.category', 'creator');
        return ApiResponse::success('Evaluation template fetched successfully.', new EvaluationTemplateResource($evaluationTemplate));
    }

    public function update(CreateEvaluationTemplateRequest $request, EvaluationTemplate $evaluationTemplate): JsonResponse
    {
        Gate::authorize('update', $evaluationTemplate);
        $template = $this->evaluationTemplateService->update($request->validated(), $evaluationTemplate);
        return ApiResponse::success('Evaluation template updated successfully.', new EvaluationTemplateResource($template));
    }

    public function destroy(EvaluationTemplate $evaluationTemplate): JsonResponse
    {
        Gate::authorize('delete', $evaluationTemplate);
        $this->evaluationTemplateService->delete($evaluationTemplate);
        return ApiResponse::success('Evaluation template deleted successfully.');
    }

    public function attachCriteria(AttachCriteriaToTemplateRequest $request, EvaluationTemplate $evaluationTemplate): JsonResponse
    {
        Gate::authorize('update', $evaluationTemplate);
        $template = $this->evaluationTemplateService->attachCriteria($evaluationTemplate, $request->validated()['criteria']);
        return ApiResponse::success('Criteria attached to template successfully.', new EvaluationTemplateResource($template->load('criteria.category')));
    }

    public function detachCriteria(EvaluationTemplate $evaluationTemplate, Criteria $criteria): JsonResponse
    {
        Gate::authorize('update', $evaluationTemplate);
        $template = $this->evaluationTemplateService->detachCriteria($evaluationTemplate, $criteria);
        return ApiResponse::success('Criteria detached from template successfully.', new EvaluationTemplateResource($template->load('criteria.category')));
    }

    public function getFormOptions(): JsonResponse
    {
        if (!Gate::any(['create', 'update'], EvaluationTemplate::class)) {
            abort(403);
        }
        $formOptions = $this->evaluationTemplateService->getFormOptions();
        return ApiResponse::success('Form options fetched.', new EvaluationTemplateFormOptionsResource($formOptions));
    }
}
