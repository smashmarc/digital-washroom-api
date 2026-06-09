<?php

namespace App\Services;

use Exception;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepartmentService extends BaseService
{
    public function __construct(Department $model)
    {
        parent::__construct($model);
    }

    public function searchPaginatedList(array $params = [])
    {
        $params['searchableColumns'] = ['name'];
        return parent::list($params);
    }

    public function create(array $data): Department
    {
        return Department::create($data);
    }

    public function update(array $data, Department $department): Department
    {
        $department->update($data);
        return $department->fresh();
    }
}
