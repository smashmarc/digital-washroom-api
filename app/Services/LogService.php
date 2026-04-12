<?php

namespace App\Services;

use App\Models\Role;
use Exception;
use App\Models\Log as LogModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogService extends BaseService
{

    const STATUS_MAP = [
        0 => 'not cleaned',
        1 => 'partially cleaned',
        2 => 'fully cleaned',
    ];

    public function __construct(LogModel $model)
    {
        parent::__construct($model);
    }

    /**
     * List roles with custom searchable columns.
     */
    public function searchPaginatedList(array $params = [])
    {
        $params['searchableColumns'] = ['room.name', 'room.location.name', 'user.name', 'note'];

        if (isset($params['search']) && $params['search'] !== '') {
            $code = array_search(strtolower($params['search']), self::STATUS_MAP);

            if ($code !== false) {
                $params['searchableColumns'] = ['note_code'];
                $params['search'] = $code;
                $params['exact'] = true;
            }
        }

        return parent::list($params);
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
