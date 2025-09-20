<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\RoomResource;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;

class RoomsController extends Controller
{
    /**
     * Get a paginated list of rooms with their locations.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $rooms = Room::with('location')->paginate(10);
        return ApiResponse::paginated('fetched successfully', $rooms, RoomResource::class);
    }

    /**
     * Store a newly created room.
     *
     * @param  StoreRoomRequest  $request
     * @return JsonResponse
     */
    public function store(StoreRoomRequest $request): JsonResponse
    {
        $room = Room::create($request->validated());
        return ApiResponse::success('Room created successfully', new RoomResource($room), 201);
    }

    /**
     * Display the specified room with its location.
     *
     * @param  Room  $room
     * @return JsonResponse
     */
    public function show(Room $room): JsonResponse
    {
        $room->load('location');

        return ApiResponse::success('Room fetched successfully', new RoomResource($room));
    }

    /**
     * Update the specified room.
     *
     * @param  UpdateRoomRequest  $request
     * @param  Room  $room
     * @return JsonResponse
     */
    public function update(UpdateRoomRequest $request, Room $room): JsonResponse
    {
        $room->update($request->validated());
        return ApiResponse::success('Room updated successfully', new RoomResource($room));
    }

    /**
     * Remove the specified room.
     *
     * @param  Room  $room
     * @return JsonResponse
     */
    public function destroy(Room $room): JsonResponse
    {
        $room->delete();
        return ApiResponse::success('Room deleted successfully', null, 200);
    }


    public function search(Request $request)
    {
        $query = Room::query();

        // Filters
        if ($request->filled('code')) {
            $query->where('qr_code', $request->code);
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Eager load allowed relations
        $allowedRelations = ['logs.user', 'lastCleanedLog'];
        $with = array_intersect(
            explode(',', $request->get('with', '')),
            $allowedRelations
        );
        $query->with($with);

        // Decide single or multiple result
        if ($request->boolean('is_single')) {
            $result = $query->firstOrFail();
            return ApiResponse::success('Room fetched successfully', new RoomResource($result));
        }

        $result = $query->get();
        return ApiResponse::success('Rooms fetched successfully', RoomResource::collection($result));
    }
}
