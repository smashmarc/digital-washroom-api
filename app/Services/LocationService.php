<?php

namespace App\Services;

use App\Models\Location;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
            $data['logo'] = $data['logo']->store('locations/logos', 'public');
        }
        return Location::create($data);
    }

    public function update(array $data, Location $model)
    {

        if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
            // Delete old file if exists
            if ($model->logo) Storage::disk('public')->delete($model->logo);
            $data['logo'] = $data['logo']->store('locations/logos', 'public');
        }

        if (!empty($data['remove_logo'])) {
            if ($model->logo) Storage::disk('public')->delete($model->logo);
            $data['logo'] = null;
        }

        unset($data['remove_logo']);
        return $model->update($data);
    }
}
