<?php

namespace App\Services;

use App\Models\CriteriaCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CriteriaCategoryService extends BaseService
{
    public function __construct(CriteriaCategory $criteriaCategory)
    {
        parent::__construct($criteriaCategory);
    }

    public function searchPaginatedList(array $params = [])
    {
        $params['searchableColumns'] = ['name', 'description', 'slug'];
        return parent::list($params);
    }

    public function create(array $data): CriteriaCategory
    {
        DB::beginTransaction();
        try {
            $category = CriteriaCategory::create([
                'name'        => $data['name'],
                'slug'        => $data['slug'] ?? Str::slug($data['name']),
                'description' => $data['description'] ?? null,
                'is_active'   => $data['is_active'] ?? true,
            ]);

            DB::commit();
            return $category;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CriteriaCategoryService::create failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'data' => $data]);
            throw $e;
        }
    }

    public function update(array $data, CriteriaCategory $category): CriteriaCategory
    {
        DB::beginTransaction();
        try {
            $category->name        = $data['name'];
            $category->slug        = $data['slug'] ?? Str::slug($data['name']);
            $category->description = $data['description'] ?? $category->description;
            $category->is_active   = $data['is_active'] ?? $category->is_active;
            $category->save();

            DB::commit();
            return $category;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CriteriaCategoryService::update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'data' => $data, 'category_id' => $category->id]);
            throw $e;
        }
    }

    public function delete(CriteriaCategory $category): void
    {
        $category->delete();
    }

    public function getFormOptions(): array
    {
        return [
            'categories' => CriteriaCategory::where('is_active', true)->get(),
        ];
    }
}
