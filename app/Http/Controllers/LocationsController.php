<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Http\Requests\CreateLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Http\Resources\LocationResource;

class LocationsController extends Controller
{
    public function index()
    {
        return LocationResource::collection(Location::all());
    }

    public function store(CreateLocationRequest $request)
    {
        $location = Location::create($request->validated());
        return new LocationResource($location);
    }

    public function show(Location $location)
    {
        return new LocationResource($location);
    }

    public function update(UpdateLocationRequest $request, Location $location)
    {
        $location->update($request->validated());
        return new LocationResource($location);
    }

    public function destroy(Location $location)
    {
        $location->delete();
        return response()->json(null, 204);
    }
}
