<?php

namespace App\Services;

use App\Models\Location;
use Exception;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LocationService extends BaseService
{
    public function __construct(Location $model)
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

    


    public function create(array $data): Location
    {   
        try {
            return Location::create($data);       
        } catch (Exception $e) {        
        Log::error('Failed to create role: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function update(array $data, Location $model)
    {     
        try {
                   
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
