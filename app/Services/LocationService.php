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

    /**
     * List roles with custom searchable columns.
     */
    public function searchPaginatedList(array $params = [])
    {
        $params['searchableColumns'] = ['name', 'address'];
        return parent::list($params);
    }




    public function create(array $data): Location
    {
        try {
            if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
                $data['logo'] = $data['logo']->store('locations/logos', 'public');
            }
            return Location::create($data);
        } catch (Exception $e) {
            Log::error('Failed to create location: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function update(array $data, Location $model)
    {
        try {
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
        } catch (\Exception $e) {

            Log::error('UserService update failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $data,
                'user_id' => $model->id,
            ]);
            throw $e;
        }
    }

    // public function delete(Role $role): void
    // {
    //     try {
    //         $role->delete();
    //     } catch (Exception $e) {
    //         Log::error("Failed to delete role {$role->id}: " . $e->getMessage(), [
    //             'trace' => $e->getTraceAsString(),
    //         ]);
    //         throw $e;
    //     }
    // }
}
