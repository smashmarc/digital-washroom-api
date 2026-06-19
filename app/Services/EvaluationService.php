<?php

namespace App\Services;

use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Models\EvaluationDepartment;
use App\Models\EvaluationTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
                  ->orWhere('result', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('evaluator', fn($e) => $e->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('template', fn($t) => $t->where('name', 'like', "%{$search}%"));
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
            $template = EvaluationTemplate::findOrFail($data['evaluation_template_id']);

            $user = User::with('departments')->findOrFail($data['user_id']);

            $evaluation = Evaluation::create([
                'user_id'                => $data['user_id'],
                'location_id'            => $user->location_id,
                'unit_id'                => $data['unit_id'] ?? null,
                'room_name'              => $data['room_name'] ?? null,
                'evaluation_template_id' => $template->id,
                'evaluator_id'           => auth()->id(),
                'pass_score'             => $template->pass_score,
                'overall_notes'          => $data['overall_notes'] ?? null,
                'status'                 => 'draft',
            ]);

            foreach ($user->departments as $dept) {
                /** @var \App\Models\Department $dept */
                EvaluationDepartment::create([
                    'evaluation_id' => $evaluation->id,
                    'department_id' => $dept->id,
                    'name'          => $dept->name,
                ]);
            }

            DB::commit();
            return $evaluation->load(['user', 'template', 'evaluator', 'evaluationDepartments']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EvaluationService::create failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'data' => $data]);
            throw $e;
        }
    }

    public function update(array $data, Evaluation $evaluation): Evaluation
    {
        DB::beginTransaction();
        try {
            $evaluation->overall_notes = $data['overall_notes'] ?? $evaluation->overall_notes;
            $evaluation->updated_by    = auth()->id();
            $evaluation->save();

            DB::commit();
            return $evaluation->load(['user', 'template', 'evaluator', 'evaluationDepartments', 'answers.criteria']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EvaluationService::update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'data' => $data, 'evaluation_id' => $evaluation->id]);
            throw $e;
        }
    }

    public function saveAnswers(Evaluation $evaluation, array $answers): Evaluation
    {
        DB::beginTransaction();
        try {
            $criteriaIds = array_column($answers, 'criteria_id');
            $criteriaMap = Criteria::whereIn('id', $criteriaIds)->get()->keyBy('id');

            foreach ($answers as $answerData) {
                $c        = $criteriaMap->get($answerData['criteria_id']);
                $snapshot = $c?->text;

                EvaluationAnswer::updateOrCreate(
                    [
                        'evaluation_id' => $evaluation->id,
                        'criteria_id'   => $answerData['criteria_id'],
                    ],
                    [
                        'value'              => $answerData['value'],
                        'notes'              => $answerData['notes'] ?? null,
                        'criteria_snapshot'  => $snapshot,
                    ]
                );
            }

            $evaluation->updated_by = auth()->id();
            $evaluation->save();

            $this->recalculateScore($evaluation);

            DB::commit();
            return $evaluation->load(['answers.criteria', 'template']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EvaluationService::saveAnswers failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'evaluation_id' => $evaluation->id, 'answers' => $answers]);
            throw $e;
        }
    }

    public function updateAnswer(array $data, EvaluationAnswer $answer): EvaluationAnswer
    {
        DB::beginTransaction();
        try {
            $answer->value = $data['value'];
            $answer->notes = $data['notes'] ?? $answer->notes;
            $answer->save();

            $this->recalculateScore($answer->evaluation);

            DB::commit();
            return $answer->load('criteria');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EvaluationService::updateAnswer failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'data' => $data, 'answer_id' => $answer->id]);
            throw $e;
        }
    }

    public function submit(Evaluation $evaluation): Evaluation
    {
        DB::beginTransaction();
        try {
            if ($evaluation->status === 'submitted') {
                throw new \Exception('Evaluation has already been submitted.');
            }

            $eligible = EvaluationAnswer::where('evaluation_id', $evaluation->id)
                ->whereIn('value', ['pass', 'fail'])
                ->count();

            if ($eligible === 0) {
                throw ValidationException::withMessages([
                    'answers' => ['At least one Pass or Fail answer is required before submitting.'],
                ]);
            }

            $this->recalculateScore($evaluation);
            $evaluation->refresh();

            $evaluation->status       = 'submitted';
            $evaluation->submitted_at = now();
            $evaluation->updated_by   = auth()->id();
            $evaluation->save();

            DB::commit();
            return $evaluation->load(['answers.criteria', 'user', 'template', 'evaluator', 'evaluationDepartments']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EvaluationService::submit failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'evaluation_id' => $evaluation->id]);
            throw $e;
        }
    }

    protected function recalculateScore(Evaluation $evaluation): void
    {
        $answers  = EvaluationAnswer::where('evaluation_id', $evaluation->id)->get();
        $eligible = $answers->filter(fn($a) => $a->value !== 'na');

        if ($eligible->isEmpty()) {
            $evaluation->score  = 0;
            $evaluation->result = 'failed';
            $evaluation->save();
            return;
        }

        $passCount  = $eligible->where('value', 'pass')->count();
        $totalCount = $eligible->count();
        $score      = round(($passCount / $totalCount) * 100, 2);

        $passThreshold = $evaluation->pass_score ?? 80;

        $evaluation->score  = $score;
        $evaluation->result = $score >= $passThreshold ? 'passed' : 'failed';
        $evaluation->save();
    }

    public function getFormOptions(): array
    {
        return [
            'answer_values' => ['pass', 'fail', 'na'],
            'statuses'      => ['draft', 'submitted'],
            'users'         => User::with('departments')->orderBy('name', 'asc')->get(),
            'templates'     => EvaluationTemplate::where('is_active', true)->orderBy('name', 'asc')->get(['id', 'name', 'description', 'pass_score']),
            'units'         => \App\Models\Unit::orderBy('name', 'asc')->get(['id', 'name', 'location_id']),
            'rooms'         => \App\Models\Room::orderBy('name', 'asc')->get(['id', 'name', 'location_id']),
            'locations'     => \App\Models\Location::orderBy('name', 'asc')->get(['id', 'name']),
        ];
    }
}
