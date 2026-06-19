<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\UnitService;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\UnitResource;
use App\Http\Requests\CreateUnitRequest;
use App\Http\Requests\UpdateUnitRequest;

class UnitsController extends Controller
{
    protected UnitService $unitService;

    public function __construct(UnitService $unitService)
    {
        $this->unitService = $unitService;
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('view', new Unit());
        $params = $request->only(['search', 'sort_by', 'sort_dir', 'per_page']);
        $items = $this->unitService->searchPaginatedList($params);
        return ApiResponse::success('Units fetched successfully.', $items, 200, UnitResource::class);
    }

    public function store(CreateUnitRequest $request): JsonResponse
    {
        $item = $this->unitService->create($request->validated());
        return ApiResponse::success('Unit created successfully.', new UnitResource($item), 201);
    }

    public function show(Unit $unit): JsonResponse
    {
        Gate::authorize('view', new Unit());
        return ApiResponse::success('Unit fetched successfully.', new UnitResource($unit->load('location')));
    }

    public function update(UpdateUnitRequest $request, Unit $unit): JsonResponse
    {
        $item = $this->unitService->update($request->validated(), $unit);
        return ApiResponse::success('Unit updated successfully.', new UnitResource($item));
    }

    public function destroy(Unit $unit): JsonResponse
    {
        Gate::authorize('delete', $unit);
        $unit->delete();
        return ApiResponse::success('Unit deleted successfully.', null, 200);
    }
}
