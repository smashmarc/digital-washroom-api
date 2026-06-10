<?php

namespace App\Services;

use App\Models\CriteriaCategory;
use App\Models\Criteria;
use App\Models\EvaluationTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CriteriaService extends BaseService
{
    public function __construct(Criteria $criteria)
    {
        parent::__construct($criteria);
    }

    public function searchPaginatedList(array $params = [])
    {
        $params['searchableColumns'] = ['text', 'category.name'];
        return parent::list($params);
    }

    public function create(array $data): Criteria
    {
        DB::beginTransaction();
        try {
            $criteria = Criteria::create([
                'criteria_category_id' => $data['criteria_category_id'],
                'text'                 => $data['text'],
                'is_active'            => $data['is_active'] ?? true,
            ]);

            if (isset($data['template_ids']) && is_array($data['template_ids'])) {
                $criteria->evaluationTemplates()->sync($data['template_ids']);
            }

            DB::commit();
            return $criteria->load(['category', 'evaluationTemplates']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CriteriaService::create failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'data' => $data]);
            throw $e;
        }
    }

    public function update(array $data, Criteria $criteria): Criteria
    {
        DB::beginTransaction();
        try {
            $criteria->criteria_category_id = $data['criteria_category_id'] ?? $criteria->criteria_category_id;
            $criteria->text                 = $data['text'];
            $criteria->is_active            = $data['is_active'] ?? $criteria->is_active;
            $criteria->save();

            if (array_key_exists('template_ids', $data)) {
                $criteria->evaluationTemplates()->sync($data['template_ids'] ?? []);
            }

            DB::commit();
            return $criteria->load(['category', 'evaluationTemplates']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CriteriaService::update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'data' => $data, 'criteria_id' => $criteria->id]);
            throw $e;
        }
    }

    public function delete(Criteria $criteria): void
    {
        $criteria->delete();
    }

    public function getFormOptions(): array
    {
        return [
            'categories' => CriteriaCategory::where('is_active', true)->orderBy('name', 'asc')->get(),
            'templates'  => EvaluationTemplate::where('is_active', true)->orderBy('name', 'asc')->get(['id', 'name']),
        ];
    }
}
