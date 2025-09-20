<?php

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

abstract class BaseService
{
    /**
     * The Eloquent model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Generic list method with search, sorting, pagination, and relationships.
     *
     * @param array $params
     *  - search: string
     *  - sort_by: string
     *  - sort_dir: asc|desc
     *  - per_page: int
     *  - with: array
     * @param array $searchableColumns
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(array $params = [])
    {
        $searchableColumns = $params['columns'] ?? ['name'];
        $exact = filter_var($params['exact'] ?? false, FILTER_VALIDATE_BOOLEAN);

        try {
            /** @var \Illuminate\Database\Eloquent\Builder $query */
            $query = $this->model->newQuery();

            // Eager load relationships
            if (!empty($params['with']) && is_array($params['with'])) {
                $query->with($params['with']);
            }

            // Search
            if (!empty($params['search'])) {
                // Trim any quotes from frontend
                $search = trim($params['search'], "'\"");

                $query->where(function ($q) use ($search, $searchableColumns, $exact) {
                    foreach ($searchableColumns as $column) {
                        if ($exact) {
                            $q->orWhere($column, '=', $search);
                        } else {
                            // Partial match
                            $q->orWhere($column, 'like', "%{$search}%");
                        }
                    }
                });
            }

            // Sorting
            $sortBy = $params['sort_by'] ?? 'id';
            $sortDir = $params['sort_dir'] ?? 'asc';
            $query->orderBy($sortBy, $sortDir);

            // Pagination
            $perPage = $params['per_page'] ?? 10;

            return $query->paginate($perPage);
        } catch (Exception $e) {
            Log::error('BaseService list failed: ' . $e->getMessage(), [
                'params' => $params,
                'model' => get_class($this->model),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
