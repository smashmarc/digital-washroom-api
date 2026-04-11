<?php

namespace App\Services;

use App\Models\Role;
use Exception;
use App\Models\Log as LogModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogService extends BaseService
{
    public function __construct(LogModel $model)
    {
        parent::__construct($model);
    }

    /**
     * List roles with custom searchable columns.
     */
    public function searchPaginatedList(array $params = [], $columns = [])
    {
        return parent::list($params, $columns);
    }


    public function create(array $data): LogModel
    {
        try {
            return LogModel::create($data);
        } catch (Exception $e) {
            Log::error('Failed to create role: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function update(array $data, LogModel $log): LogModel
    {
        try {
            $log->update($data);
            return $log;
        } catch (Exception $e) {
            Log::error('Failed to update log: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $data,
                'log_id' => $log->id,
            ]);
            throw $e;
        }
    }

    public function delete(LogModel $log): void
    {
        try {
            $log->delete();
        } catch (Exception $e) {
            Log::error("Failed to delete log {$log->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
