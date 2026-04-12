<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Location;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Services\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
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
        //Gate::authorize('create', User::class);
        try {
            $item = $this->locationService->create($request->validated());
            return ApiResponse::success(
                'Location created successfully.',
                new LocationResource($item),
                201,
                null
            );
        } catch (Exception $e) {
            return ApiResponse::error('Something went wrong. please contact your administrator', 500);
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
        $location->update($request->validated());

        return ApiResponse::success('Location updated successfully', new LocationResource($location));
    }

    /**
     * Remove the specified location.
     *
     * @param  Location  $location
     * @return JsonResponse
     */
    public function destroy(Location $location): JsonResponse
    {
        Gate::authorize('delete', new Location());
        $location->delete();

        return ApiResponse::success('Location deleted successfully', null, 200);
    }
}
