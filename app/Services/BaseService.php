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

    protected function applyWith($query, array $params): void
    {
        if (!empty($params['with']) && is_array($params['with'])) {
            $query->with($params['with']);
        }
    }

    protected function applySearch($query, array $params): void
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

    protected function applyDateRange($query, array $params): void
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

}
