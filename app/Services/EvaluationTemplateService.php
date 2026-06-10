<?php

namespace App\Services;

use App\Models\Criteria;
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
        $params['withCount']         = ['criteria'];
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
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EvaluationTemplateService::create failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'data' => $data]);
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
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EvaluationTemplateService::update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'data' => $data, 'template_id' => $template->id]);
            throw $e;
        }
    }

    public function delete(EvaluationTemplate $template): void
    {
        $template->delete();
    }

    public function attachCriteria(EvaluationTemplate $template, array $criteria): EvaluationTemplate
    {
        DB::beginTransaction();
        try {
            $syncData = [];
            foreach ($criteria as $item) {
                $syncData[$item['criteria_id']] = [
                    'order' => $item['order'] ?? 0,
                ];
            }

            $template->criteria()->syncWithoutDetaching($syncData);

            DB::commit();
            return $template->load('criteria');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EvaluationTemplateService::attachCriteria failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'template_id' => $template->id, 'criteria' => $criteria]);
            throw $e;
        }
    }

    public function detachCriteria(EvaluationTemplate $template, Criteria $criteria): EvaluationTemplate
    {
        $template->criteria()->detach($criteria->id);
        return $template->load('criteria');
    }

    public function getFormOptions(): array
    {
        return [
            'criteria' => Criteria::where('is_active', true)->with('category')->get(),
        ];
    }
}
