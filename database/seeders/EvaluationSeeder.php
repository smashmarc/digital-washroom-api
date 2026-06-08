<?php

namespace Database\Seeders;

use App\Constants\Role as RoleConstant;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Models\EvaluationTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class EvaluationSeeder extends Seeder
{
    public function run(): void
    {
        $users     = User::all();
        $templates = EvaluationTemplate::with('criteria')->get();
        $evaluator = User::role(RoleConstant::ADMINISTRATOR)->first() ?? $users->first();

        // 3 months ago, 2 months ago, 1 month ago
        $months = [
            now()->subMonths(3),
            now()->subMonths(2),
            now()->subMonths(1),
        ];

        foreach ($users as $user) {
            foreach ($templates as $template) {
                foreach ($months as $month) {
                    $submittedAt = $month->copy()->endOfMonth();

                    $answers = $this->generateAnswers($template->criteria);
                    [$score, $result] = $this->calculateScore($answers, $template->pass_score);

                    $evaluation = Evaluation::create([
                        'user_id'                => $user->id,
                        'evaluation_template_id' => $template->id,
                        'evaluator_id'           => $evaluator->id,
                        'pass_score'             => $template->pass_score,
                        'score'                  => $score,
                        'result'                 => $result,
                        'status'                 => 'submitted',
                        'submitted_at'           => $submittedAt,
                        'created_at'             => $submittedAt,
                        'updated_at'             => $submittedAt,
                    ]);

                    $answerRows = [];
                    foreach ($answers as $criteriaId => $value) {
                        $criteria = $template->criteria->firstWhere('id', $criteriaId);
                        $answerRows[] = [
                            'evaluation_id'     => $evaluation->id,
                            'criteria_id'       => $criteriaId,
                            'criteria_snapshot' => $criteria?->text,
                            'value'             => $value,
                            'notes'             => null,
                            'created_at'        => $submittedAt,
                            'updated_at'        => $submittedAt,
                        ];
                    }

                    EvaluationAnswer::insert($answerRows);
                }
            }
        }
    }

    private function generateAnswers(\Illuminate\Database\Eloquent\Collection $criteria): array
    {
        $answers = [];
        foreach ($criteria as $c) {
            // Weight toward realistic pass-heavy results
            $answers[$c->id] = fake()->randomElement(['pass', 'pass', 'pass', 'fail', 'na']);
        }
        return $answers;
    }

    private function calculateScore(array $answers, int $passScore): array
    {
        $pass = count(array_filter($answers, fn ($v) => $v === 'pass'));
        $fail = count(array_filter($answers, fn ($v) => $v === 'fail'));
        $scored = $pass + $fail;

        if ($scored === 0) {
            return [null, 'inconclusive'];
        }

        $score  = round(($pass / $scored) * 100, 1);
        $result = $score >= $passScore ? 'passed' : 'failed';

        return [$score, $result];
    }
}
