<?php

namespace App\Services;

use Exception;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Models\EvaluationTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EvaluationService extends BaseService
{
    public function __construct(Evaluation $evaluation)
    {
        parent::__construct($evaluation);
    }

    public function searchPaginatedList(array $params = [])
    {
        $sortDir = in_array(strtolower($params['sort_dir'] ?? ''), ['asc', 'desc'])
            ? strtolower($params['sort_dir'])
            : 'desc';

        $query = Evaluation::query();

        if (!empty($params['user_id'])) {
            $query->where('user_id', (int) $params['user_id']);
        }

        if (!empty($params['with']) && is_array($params['with'])) {
            $query->with($params['with']);
        }

        if (!empty($params['date_from'])) {
            $query->whereDate('submitted_at', '>=', $params['date_from']);
        }
        if (!empty($params['date_to'])) {
            $query->whereDate('submitted_at', '<=', $params['date_to']);
        }

        if (!empty($params['search'])) {
            $search = trim($params['search']);
            $query->where(function ($q) use ($search) {
                $q->where('status', 'like', "%{$search}%")
                  ->orWhere('result', 'like', "%{$search}%");
            });
        }

        return $query->orderBy($params['sort_by'] ?? 'id', $sortDir)
                     ->paginate(max(1, (int) ($params['per_page'] ?? 10)));
    }

    public function create(array $data): Evaluation
    {
        DB::beginTransaction();

        try {
            /** @var EvaluationTemplate $template */
            /** @var EvaluationTemplate $template */
            $template = EvaluationTemplate::findOrFail($data['evaluation_template_id']);

            $evaluation = Evaluation::create([
                'user_id'                => $data['user_id'],
                'evaluation_template_id' => $template->id,
                'evaluator_id'           => auth()->id(),
                'pass_score'             => $template->pass_score,
                'overall_notes'          => $data['overall_notes'] ?? null,
                'status'                 => 'draft',
            ]);

            DB::commit();
            return $evaluation->load(['user', 'template', 'evaluator']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('EvaluationService::create failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data'  => $data,
            ]);
            throw $e;
        }
    }

    public function update(array $data, Evaluation $evaluation): Evaluation
    {
        DB::beginTransaction();

        try {
            $evaluation->overall_notes = $data['overall_notes'] ?? $evaluation->overall_notes;
            $evaluation->save();

            DB::commit();
            return $evaluation->load(['user', 'template', 'evaluator', 'answers.question']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('EvaluationService::update failed: ' . $e->getMessage(), [
                'trace'         => $e->getTraceAsString(),
                'data'          => $data,
                'evaluation_id' => $evaluation->id,
            ]);
            throw $e;
        }
    }

    /**
     * Save or update individual answers for an evaluation.
     * Recalculates score after every save.
     * Payload: [['question_id' => 1, 'value' => 'pass|fail|na', 'notes' => '...'], ...]
     */
    public function saveAnswers(Evaluation $evaluation, array $answers): Evaluation
    {
        DB::beginTransaction();

        try {
            foreach ($answers as $answerData) {
                EvaluationAnswer::updateOrCreate(
                    [
                        'evaluation_id' => $evaluation->id,
                        'question_id'   => $answerData['question_id'],
                    ],
                    [
                        'value' => $answerData['value'],
                        'notes' => $answerData['notes'] ?? null,
                    ]
                );
            }

            // Recalculate score after every answer update
            $this->recalculateScore($evaluation);

            DB::commit();
            return $evaluation->load(['answers.question', 'template']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('EvaluationService::saveAnswers failed: ' . $e->getMessage(), [
                'trace'         => $e->getTraceAsString(),
                'evaluation_id' => $evaluation->id,
                'answers'       => $answers,
            ]);
            throw $e;
        }
    }

    /**
     * Update a single answer by its own ID.
     */
    public function updateAnswer(array $data, EvaluationAnswer $answer): EvaluationAnswer
    {
        DB::beginTransaction();

        try {
            $answer->value = $data['value'];
            $answer->notes = $data['notes'] ?? $answer->notes;
            $answer->save();

            // Recalculate score after answer change
            $this->recalculateScore($answer->evaluation);

            DB::commit();
            return $answer->load('question');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('EvaluationService::updateAnswer failed: ' . $e->getMessage(), [
                'trace'     => $e->getTraceAsString(),
                'data'      => $data,
                'answer_id' => $answer->id,
            ]);
            throw $e;
        }
    }

    /**
     * Submit the evaluation — locks it, calculates final score and result.
     */
    public function submit(Evaluation $evaluation): Evaluation
    {
        DB::beginTransaction();

        try {
            if ($evaluation->status === 'submitted') {
                throw new Exception('Evaluation has already been submitted.');
            }

            $this->recalculateScore($evaluation);
            $evaluation->refresh();

            $evaluation->status       = 'submitted';
            $evaluation->submitted_at = now();
            $evaluation->save();

            DB::commit();
            return $evaluation->load(['answers.question', 'user', 'template', 'evaluator']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('EvaluationService::submit failed: ' . $e->getMessage(), [
                'trace'         => $e->getTraceAsString(),
                'evaluation_id' => $evaluation->id,
            ]);
            throw $e;
        }
    }

    /**
     * Core scoring logic:
     * - 'na' answers are excluded from both numerator and denominator.
     * - A fatal 'fail' auto-fails the entire evaluation regardless of score.
     * - If all answers are 'na', result is 'inconclusive'.
     * - result = 'passed' | 'failed' | 'inconclusive'
     */
    protected function recalculateScore(Evaluation $evaluation): void
    {
        $answers = EvaluationAnswer::where('evaluation_id', $evaluation->id)
            ->with('question')
            ->get();

        // Separate NA from eligible
        $eligible = $answers->filter(fn($a) => $a->value !== 'na');

        if ($eligible->isEmpty()) {
            $evaluation->score        = null;
            $evaluation->result       = 'inconclusive';
            $evaluation->fatal_failed = false;
            $evaluation->save();
            return;
        }

        // Check for fatal fail — any fatal question answered 'fail' auto-fails
        $fatalFailed = $eligible->contains(
            fn($a) => $a->value === 'fail' && $a->question?->is_fatal === true
        );

        if ($fatalFailed) {
            $evaluation->score        = 0;
            $evaluation->result       = 'failed';
            $evaluation->fatal_failed = true;
            $evaluation->save();
            return;
        }

        // Normal scoring: count passes over eligible (non-NA) questions
        $passCount  = $eligible->where('value', 'pass')->count();
        $totalCount = $eligible->count();
        $score      = round(($passCount / $totalCount) * 100, 2);

        // Compare against the snapshotted pass_score recorded at evaluation creation
        $passThreshold = $evaluation->pass_score ?? 80;

        $evaluation->score        = $score;
        $evaluation->result       = $score >= $passThreshold ? 'passed' : 'failed';
        $evaluation->fatal_failed = false;
        $evaluation->save();
    }

    public function getFormOptions(): array
    {
        try {
            return [
                'answer_values' => ['pass', 'fail', 'na'],
                'statuses'      => ['draft', 'submitted'],
                'users'         => User::orderBy('name', 'asc')->get(['id', 'name', 'email']),
                'templates'     => EvaluationTemplate::where('is_active', true)->orderBy('name', 'asc')->get(['id', 'name']),
            ];
        } catch (Exception $e) {
            Log::error('EvaluationService::getFormOptions failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
