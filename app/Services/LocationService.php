<?php

namespace App\Services;

use App\Models\Location;

class LocationService extends BaseService
{
    public function __construct(Location $model)
    {
        parent::__construct($model);
    }

    public function searchPaginatedList(array $params = [])
    {
        $params['searchableColumns'] = ['name', 'address'];
        return parent::list($params);
    }

    public function create(array $data): Location
    {
        return Location::create($data);
    }

    public function update(array $data, Location $model)
    {
        return $model->update($data);
    }
}
