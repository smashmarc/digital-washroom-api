<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Http\Requests\CreateLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Http\Resources\LocationResource;
use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;

class LocationsController extends Controller
{
    /**
     * Display a paginated listing of locations.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $locations = Location::paginate(10);

        return ApiResponse::paginated('Locations fetched successfully', $locations, LocationResource::class);
    }

    /**
     * Store a newly created location.
     *
     * @param  CreateLocationRequest  $request
     * @return JsonResponse
     */
    public function store(CreateLocationRequest $request): JsonResponse
    {
        $location = Location::create($request->validated());

        return ApiResponse::success('Location created successfully', new LocationResource($location), 201);
    }

    /**
     * Display the specified location.
     *
     * @param  Location  $location
     * @return JsonResponse
     */
    public function show(Location $location): JsonResponse
    {
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
        $location->delete();

        return ApiResponse::success('Location deleted successfully', null, 200);
    }
}
