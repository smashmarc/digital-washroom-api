<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Http\Requests\UploadRoomRequest;
use App\Http\Resources\RoomFormOptionsResource;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use App\Services\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RoomsController extends Controller
{
    public function __construct(protected RoomService $roomService) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('view', new Room());
        $params = $request->only(['search', 'sort_by', 'sort_dir', 'per_page', 'with', 'columns', 'exact']);
        $items = $this->roomService->searchPaginatedList($params);
        return ApiResponse::success('Rooms fetched successfully.', $items, 200, RoomResource::class);
    }

    public function store(CreateRoomRequest $request): JsonResponse
    {
        Gate::authorize('create', Room::class);
        $room = $this->roomService->create($request->validated());
        return ApiResponse::success('Room created successfully.', new RoomResource($room), 201);
    }

    public function show(Room $room): JsonResponse
    {
        Gate::authorize('view', $room);
        $room->load(['location', 'logs.user']);
        return ApiResponse::success('Room fetched successfully.', new RoomResource($room));
    }

    public function update(UpdateRoomRequest $request, Room $room): JsonResponse
    {
        Gate::authorize('update', $room);
        $updated = $this->roomService->update($request->validated(), $room);
        return ApiResponse::success('Room updated successfully.', new RoomResource($updated));
    }

    public function destroy(Room $room): JsonResponse
    {
        Gate::authorize('delete', $room);
        $room->delete();
        return ApiResponse::success('Room deleted successfully.', null, 200);
    }

    public function getFormOptions(): JsonResponse
    {
        if (!Gate::any(['create', 'update'], Room::class)) {
            abort(403);
        }
        $formOptions = $this->roomService->getFormOptions();
        return ApiResponse::success('Form options fetched.', new RoomFormOptionsResource($formOptions), 200);
    }

    /** Public action — no authorization required. */
    public function qrView(Room $room): JsonResponse
    {
        $room->load(['location', 'lastCleanedLog']);
        return ApiResponse::success('Room fetched successfully.', new RoomResource($room));
    }

    public function upload(UploadRoomRequest $request): JsonResponse
    {
        Gate::authorize('create', Room::class);
        $createdRooms = $this->roomService->upload($request->validated()['rooms']);
        return ApiResponse::success('Rooms uploaded successfully.', $createdRooms, 201);
    }
}
