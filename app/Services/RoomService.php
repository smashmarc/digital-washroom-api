<?php

namespace App\Services;

use Exception;
use App\Models\Room;
use App\Models\Location;
use Illuminate\Support\Facades\Log;

class RoomService extends BaseService
{
    public function __construct(Room $model)
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

    public function getFormOptions()
    {        
         try {
           $items= Location::all();          
           return ['locations'=>$items];        
        } catch (Exception $e) {               
            Log::error('Failed to fetch form options ' . $e->getMessage(), [               
                'trace' => $e->getTraceAsString(),
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
