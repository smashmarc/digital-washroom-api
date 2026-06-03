<?php

namespace App\Services;

use Exception;
use App\Models\Question;
use App\Models\EvaluationTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EvaluationTemplateService extends BaseService
{
    public function __construct(EvaluationTemplate $evaluationTemplate)
    {
        parent::__construct($evaluationTemplate);
    }

    public function searchPaginatedList(array $params = [])
    {
        $params['searchableColumns'] = ['name', 'description'];
        $params['withCount']         = ['questions'];
        return parent::list($params);
    }

    public function create(array $data): EvaluationTemplate
    {
        DB::beginTransaction();

        try {
            $template = EvaluationTemplate::create([
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'pass_score'  => $data['pass_score'] ?? 80,
                'is_active'   => $data['is_active'] ?? true,
                'created_by'  => auth()->id(),
            ]);

            DB::commit();
            return $template;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('EvaluationTemplateService::create failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data'  => $data,
            ]);
            throw $e;
        }
    }

    public function update(array $data, EvaluationTemplate $template): EvaluationTemplate
    {
        DB::beginTransaction();

        try {
            $template->name        = $data['name'];
            $template->description = $data['description'] ?? $template->description;
            $template->pass_score  = $data['pass_score'] ?? $template->pass_score;
            $template->is_active   = $data['is_active'] ?? $template->is_active;
            $template->save();

            DB::commit();
            return $template;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('EvaluationTemplateService::update failed: ' . $e->getMessage(), [
                'trace'       => $e->getTraceAsString(),
                'data'        => $data,
                'template_id' => $template->id,
            ]);
            throw $e;
        }
    }

    public function delete(EvaluationTemplate $template): void
    {
        try {
            $template->delete();
        } catch (Exception $e) {
            Log::error("EvaluationTemplateService::delete failed for template {$template->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Attach questions to a template with optional order.
     * Payload: [['question_id' => 1, 'order' => 1], ...]
     */
    public function attachQuestions(EvaluationTemplate $template, array $questions): EvaluationTemplate
    {
        DB::beginTransaction();

        try {
            $syncData = [];
            foreach ($questions as $item) {
                $syncData[$item['question_id']] = [
                    'order' => $item['order'] ?? 0,
                ];
            }

            $template->questions()->syncWithoutDetaching($syncData);

            DB::commit();
            return $template->load('questions');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('EvaluationTemplateService::attachQuestions failed: ' . $e->getMessage(), [
                'trace'       => $e->getTraceAsString(),
                'template_id' => $template->id,
                'questions'   => $questions,
            ]);
            throw $e;
        }
    }

    /**
     * Detach a single question from a template.
     */
    public function detachQuestion(EvaluationTemplate $template, Question $question): EvaluationTemplate
    {
        try {
            $template->questions()->detach($question->id);
            return $template->load('questions');
        } catch (Exception $e) {
            Log::error('EvaluationTemplateService::detachQuestion failed: ' . $e->getMessage(), [
                'trace'       => $e->getTraceAsString(),
                'template_id' => $template->id,
                'question_id' => $question->id,
            ]);
            throw $e;
        }
    }

    public function getFormOptions(): array
    {
        try {
            return [
                'questions' => Question::where('is_active', true)->with('category')->get(),
            ];
        } catch (Exception $e) {
            Log::error('EvaluationTemplateService::getFormOptions failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
