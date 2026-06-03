<?php

namespace App\Services;

use Exception;
use App\Models\EvaluationTemplate;
use App\Models\Question;
use App\Models\QuestionCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuestionService extends BaseService
{
    public function __construct(Question $question)
    {
        parent::__construct($question);
    }

    public function searchPaginatedList(array $params = [])
    {
        $params['searchableColumns'] = ['text', 'category.name'];
        return parent::list($params);
    }

    public function create(array $data): Question
    {
        DB::beginTransaction();

        try {
            $question = Question::create([
                'question_category_id' => $data['question_category_id'],
                'text'                 => $data['text'],
                'is_fatal'             => $data['is_fatal'] ?? false,
                'is_active'            => $data['is_active'] ?? true,
            ]);

            if (isset($data['template_ids']) && is_array($data['template_ids'])) {
                $question->evaluationTemplates()->sync($data['template_ids']);
            }

            DB::commit();
            return $question->load(['category', 'evaluationTemplates']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('QuestionService::create failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data'  => $data,
            ]);
            throw $e;
        }
    }

    public function update(array $data, Question $question): Question
    {
        DB::beginTransaction();

        try {
            $question->question_category_id = $data['question_category_id'] ?? $question->question_category_id;
            $question->text                 = $data['text'];
            $question->is_fatal             = $data['is_fatal'] ?? $question->is_fatal;
            $question->is_active            = $data['is_active'] ?? $question->is_active;
            $question->save();

            if (array_key_exists('template_ids', $data)) {
                $question->evaluationTemplates()->sync($data['template_ids'] ?? []);
            }

            DB::commit();
            return $question->load(['category', 'evaluationTemplates']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('QuestionService::update failed: ' . $e->getMessage(), [
                'trace'       => $e->getTraceAsString(),
                'data'        => $data,
                'question_id' => $question->id,
            ]);
            throw $e;
        }
    }

    public function delete(Question $question): void
    {
        try {
            $question->delete();
        } catch (Exception $e) {
            Log::error("QuestionService::delete failed for question {$question->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function getFormOptions(): array
    {
        try {
            return [
                'categories' => QuestionCategory::where('is_active', true)->orderBy('name', 'asc')->get(),
                'templates'  => EvaluationTemplate::where('is_active', true)->orderBy('name', 'asc')->get(['id', 'name']),
            ];
        } catch (Exception $e) {
            Log::error('QuestionService::getFormOptions failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
