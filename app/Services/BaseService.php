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
        $searchableColumns = $params['searchableColumns'] ?? ['name'];
        $exact = filter_var($params['exact'] ?? false, FILTER_VALIDATE_BOOLEAN);

        try {
            /** @var \Illuminate\Database\Eloquent\Builder $query */
            $query = $this->model->newQuery();

            // Eager load relationships
            if (!empty($params['with']) && is_array($params['with'])) {
                $query->with($params['with']);
            }

            // Search
           if (isset($params['search']) && $params['search'] !== '') {

                Log::debug('not empty:'.$params['search']);
                $search = trim($params['search'], "'\"");

                $query->where(function ($q) use ($search, $searchableColumns, $exact) {
                    foreach ($searchableColumns as $column) {
                        // Check if column is a relationship (e.g. "location.name" or just "location")
                        if (str_contains($column, '.')) {
                            $parts = explode('.', $column);
                            $relColumn = array_pop($parts);       // last part = column (e.g. 'name')
                            $relations = $parts;                   // remaining = nested relations (e.g. ['room', 'location'])

                            // Safety: only check top-level relation exists on the model
                            if (!method_exists($this->model, $relations[0])) {
                                Log::warning("BaseService: relation '{$relations[0]}' does not exist on " . get_class($this->model));
                                continue;
                            }

                            // Build nested whereHas: room.location → whereHas('room', fn → whereHas('location', fn → where('name')))
                            $q->orWhereHas(implode('.', $relations), function ($rel) use ($relColumn, $search, $exact) {
                                if ($exact) {
                                    $rel->where($relColumn, '=', $search);
                                } else {
                                    $rel->where($relColumn, 'like', "%{$search}%");
                                }
                            });
                        } else {
                            $isInteger = is_numeric($search) && intval($search) == $search;
                            if ($exact || $isInteger) {
                                Log::debug('iSInteger:'.$isInteger);
                                $q->orWhere($column, '=', $search);
                            } else {
                                $q->orWhere($column, 'like', "%{$search}%");
                            }
                        }
                    }
                });
            }

            // Sorting
            $sortBy = $params['sort_by'] ?? 'id';
            $sortDir = $params['sort_dir'] ?? 'asc';
            $query->orderBy($sortBy, $sortDir);

            Log::debug('BaseService SQL: ' . vsprintf(
    str_replace('?', "'%s'", $query->toSql()),
    $query->getBindings()
));

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
