<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

abstract class BaseService
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function list(array $params = [])
    {
        $sortDir = in_array(strtolower($params['sort_dir'] ?? ''), ['asc', 'desc'])
            ? strtolower($params['sort_dir'])
            : 'asc';

        try {
            $query = $this->model->newQuery();

            $this->applyWith($query, $params);
            $this->applySearch($query, $params);
            $this->applyDateRange($query, $params);

            $query->orderBy($params['sort_by'] ?? 'id', $sortDir);

            return $query->paginate(max(1, (int) ($params['per_page'] ?? 10)));
        } catch (Exception $e) {
            Log::error('BaseService::list failed', [
                'model'   => get_class($this->model),
                'params'  => $params,
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function applyWith($query, array $params): void
    {
        if (!empty($params['with']) && is_array($params['with'])) {
            $query->with($params['with']);
        }
    }

    private function applySearch($query, array $params): void
    {
        if (!isset($params['search']) || $params['search'] === '') return;

        $search    = trim($params['search'], "'\"");
        $exact     = filter_var($params['exact'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $isInteger = is_numeric($search) && intval($search) == $search;
        $columns   = $params['searchableColumns'] ?? ['name'];

        $query->where(function ($q) use ($search, $exact, $isInteger, $columns) {
            foreach ($columns as $column) {
                str_contains($column, '.')
                    ? $this->applyRelationSearch($q, $column, $search, $exact)
                    : $this->applyColumnSearch($q, $column, $search, $exact, $isInteger);
            }
        });
    }

    private function applyDateRange($query, array $params): void
    {
        if (!empty($params['date_from'])) {
            $query->whereDate('logged_at', '>=', $params['date_from']);
        }
        if (!empty($params['date_to'])) {
            $query->whereDate('logged_at', '<=', $params['date_to']);
        }
    }

    private function applyRelationSearch($q, string $column, string $search, bool $exact): void
    {
        $parts     = explode('.', $column);
        $relColumn = array_pop($parts);
        $relation  = implode('.', $parts);

        if (!method_exists($this->model, $parts[0])) {
            Log::warning("BaseService: relation '{$parts[0]}' does not exist on " . get_class($this->model));
            return;
        }

        $q->orWhereHas(
            $relation,
            fn($rel) => $exact
                ? $rel->where($relColumn, '=', $search)
                : $rel->where($relColumn, 'like', "%{$search}%")
        );
    }

    private function applyColumnSearch($q, string $column, string $search, bool $exact, bool $isInteger): void
    {
        $exact || $isInteger
            ? $q->orWhere($column, '=', $search)
            : $q->orWhere($column, 'like', "%{$search}%");
    }

    public function listOld(array $params = [])
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

                Log::debug('not empty:' . $params['search']);
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
                                Log::debug('iSInteger:' . $isInteger);
                                $q->orWhere($column, '=', $search);
                            } else {
                                $q->orWhere($column, 'like', "%{$search}%");
                            }
                        }
                    }
                });
            }

            // Sorting
            $sortBy  = $params['sort_by'] ?? 'id';
            $sortDir = strtolower($params['sort_dir'] ?? '');
            $sortDir = in_array($sortDir, ['asc', 'desc']) ? $sortDir : 'asc';
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
