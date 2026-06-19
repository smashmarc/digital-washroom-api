<?php

namespace App\Services;

use App\Models\Unit;

class UnitService extends BaseService
{
    public function __construct(Unit $model)
    {
        parent::__construct($model);
    }

    public function searchPaginatedList(array $params = [])
    {
        $params['searchableColumns'] = ['name'];
        $params['with']              = ['location'];
        return parent::list($params);
    }

    public function create(array $data): Unit
    {
        return Unit::create($data)->load('location');
    }

    public function update(array $data, Unit $unit): Unit
    {
        $unit->update($data);
        return $unit->fresh(['location']);
    }
}
