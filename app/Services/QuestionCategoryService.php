<?php

namespace App\Services;

use Exception;
use App\Models\QuestionCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QuestionCategoryService extends BaseService
{
    public function __construct(QuestionCategory $questionCategory)
    {
        parent::__construct($questionCategory);
    }

    public function searchPaginatedList(array $params = [])
    {
        $params['searchableColumns'] = ['name', 'description', 'slug'];
        return parent::list($params);
    }

    public function create(array $data): QuestionCategory
    {
        DB::beginTransaction();

        try {
            $category = QuestionCategory::create([
                'name'        => $data['name'],
                'slug'        => $data['slug'] ?? Str::slug($data['name']),
                'description' => $data['description'] ?? null,
                'is_active'   => $data['is_active'] ?? true,
            ]);

            DB::commit();
            return $category;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('QuestionCategoryService::create failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data'  => $data,
            ]);
            throw $e;
        }
    }

    public function update(array $data, QuestionCategory $category): QuestionCategory
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

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('QuestionCategoryService::update failed: ' . $e->getMessage(), [
                'trace'       => $e->getTraceAsString(),
                'data'        => $data,
                'category_id' => $category->id,
            ]);
            throw $e;
        }
    }

    public function delete(QuestionCategory $category): void
    {
        try {
            $category->delete();
        } catch (Exception $e) {
            Log::error("QuestionCategoryService::delete failed for category {$category->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function getFormOptions(): array
    {
        try {
            return [
                'categories' => QuestionCategory::where('is_active', true)->get(),
            ];
        } catch (Exception $e) {
            Log::error('QuestionCategoryService::getFormOptions failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
