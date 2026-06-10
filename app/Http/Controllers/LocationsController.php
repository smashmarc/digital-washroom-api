<?php

namespace App\Http\Controllers;

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
        $params = $request->only(['search', 'sort_by', 'sort_dir', 'per_page', 'with', 'columns', 'exact']);
        $items = $this->locationService->searchPaginatedList($params);
        return ApiResponse::success('Locations fetched successfully.', $items, 200, LocationResource::class);
    }

    public function store(CreateLocationRequest $request): JsonResponse
    {
        Gate::authorize('create', new Location());
        $item = $this->locationService->create($request->validated());
        return ApiResponse::success('Location created successfully.', new LocationResource($item), 201);
    }

    public function show(Location $location): JsonResponse
    {
        Gate::authorize('view', new Location());
        return ApiResponse::success('Location fetched successfully.', new LocationResource($location));
    }

    public function update(UpdateLocationRequest $request, Location $location): JsonResponse
    {
        Gate::authorize('update', $location);
        $this->locationService->update($request->validated(), $location);
        return ApiResponse::success('Location updated successfully.', new LocationResource($location->fresh()));
    }

    public function destroy(Location $location): JsonResponse
    {
        Gate::authorize('delete', $location);
        $location->delete();
        return ApiResponse::success('Location deleted successfully.', null, 200);
    }
}
