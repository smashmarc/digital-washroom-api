<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Location;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Services\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\LocationResource;
use App\Http\Requests\CreateLocationRequest;
use App\Http\Requests\UpdateLocationRequest;

class LocationsController extends Controller
{
    protected $locationService;
    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;     
    }
    
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('view', new Location());
        $params = $request->only([
            'search',
            'sort_by',
            'sort_dir',
            'per_page',
            'with',
            'columns',
            'exact'
        ]);
       
        try {
            $items = $this->locationService->searchPaginatedList($params);
            return ApiResponse::success(
                'Location fetched successfully.',
                $items,
                200,
                LocationResource::class
            );
           
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch roles.', 500);
        }
    }

    public function store(CreateLocationRequest $request): JsonResponse
    {
        Gate::authorize('create', new Location());
        try {
            $item = $this->locationService->create($request->validated());
            return ApiResponse::success('Location created successfully.', new LocationResource($item), 201);
        } catch (Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to create location.', 500);
        }
    }
    /**
     * Display the specified location.
     *
     * @param  Location  $location
     * @return JsonResponse
     */
    public function show(Location $location): JsonResponse
    {
        Gate::authorize('view', new Location());
        return ApiResponse::success('Location fetched successfully', new LocationResource($location));
    }

    /**
     * Update the specified location.
     *
     * @param  UpdateLocationRequest  $request
     * @param  Location  $location
     * @return JsonResponse
     */
    public function update(UpdateLocationRequest $request, Location $location): JsonResponse
    {
        Gate::authorize('update', $location);
        try {
            $this->locationService->update($request->validated(), $location);
            return ApiResponse::success('Location updated successfully', new LocationResource($location->fresh()));
        } catch (Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to update location.', 500);
        }
    }

    public function destroy(Location $location): JsonResponse
    {
        Gate::authorize('delete', $location);
        try {
            $location->delete();
            return ApiResponse::success('Location deleted successfully', null, 200);
        } catch (Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to delete location.', 500);
        }
    }
}
