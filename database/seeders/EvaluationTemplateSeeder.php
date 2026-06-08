<?php

namespace Database\Seeders;

use App\Constants\Role as RoleConstant;
use App\Models\Criteria;
use App\Models\EvaluationTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class EvaluationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role(RoleConstant::ADMINISTRATOR)->first();
        $createdBy = $admin?->id ?? User::first()?->id;

        $allCriteria = Criteria::where('is_active', true)->get();

        $templates = [
            [
                'name'        => 'Monthly Washroom Inspection',
                'description' => 'Comprehensive monthly inspection covering all categories.',
                'pass_score'  => 80,
                'criteria'    => $allCriteria->pluck('id')->toArray(),
            ],
            [
                'name'        => 'Daily Quick Check',
                'description' => 'Fast daily walkthrough focusing on cleanliness and supplies.',
                'pass_score'  => 75,
                'criteria'    => $allCriteria->filter(fn ($c) => in_array($c->text, [
                    'Floors are clean, dry, and free of debris.',
                    'Toilets and urinals are clean inside and out.',
                    'Liquid soap dispensers are stocked and functional.',
                    'Toilet paper is adequately stocked in all cubicles.',
                    'No unpleasant odours are detectable upon entry.',
                    'All lighting is functional with no blown bulbs.',
                    'Waste bins are emptied and liners are in place.',
                ]))->pluck('id')->toArray(),
            ],
            [
                'name'        => 'Quarterly Deep Audit',
                'description' => 'Full facility audit including maintenance and safety checks.',
                'pass_score'  => 85,
                'criteria'    => $allCriteria->pluck('id')->toArray(),
            ],
        ];

        foreach ($templates as $data) {
            $template = EvaluationTemplate::firstOrCreate(
                ['name' => $data['name']],
                [
                    'description' => $data['description'],
                    'pass_score'  => $data['pass_score'],
                    'is_active'   => true,
                    'created_by'  => $createdBy,
                ]
            );

            $sync = collect($data['criteria'])->values()->mapWithKeys(
                fn ($id, $index) => [$id => ['order' => $index + 1]]
            )->toArray();

            $template->criteria()->sync($sync);
        }
    }
}
