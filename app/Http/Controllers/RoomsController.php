<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Room;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Services\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\RoomResource;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\CreateRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Http\Requests\UploadRoomRequest;
use App\Http\Resources\RoomFormOptionsResource;

class RoomsController extends Controller
{
    protected $roomService;
    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('view', Room::class);
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
            $items = $this->roomService->searchPaginatedList($params);
            return ApiResponse::success(
                'Location fetched successfully.',
                $items,
                200,
                RoomResource::class
            );
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch rooms.'. $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created room.
     *
     * @param  CreateRoomRequest  $request
     * @return JsonResponse
     */
    public function store(CreateRoomRequest $request): JsonResponse
    {
        Gate::authorize('create', Room::class);
        try {
            $room = $this->roomService->create($request->validated());
            return ApiResponse::success('Room created successfully', new RoomResource($room), 201);
        } catch (Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to create room. ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified room with its location.
     *
     * @param  Room  $room
     * @return JsonResponse
     */
    public function show(Room $room): JsonResponse
    {
        Gate::authorize('view', Room::class);
        $room->load(['location', 'logs.user']);

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
        Gate::authorize('update', Room::class);
        try {
            $updated = $this->roomService->update($request->validated(), $room);
            return ApiResponse::success('Room updated successfully', new RoomResource($updated));
        } catch (Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to update room. ' . $e->getMessage(), 500);
        }
    }

    public function destroy(Room $room): JsonResponse
    {
        Gate::authorize('delete', $room);
        try {
            $room->delete();
            return ApiResponse::success('Room deleted successfully', null, 200);
        } catch (Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to delete room. ' . $e->getMessage(), 500);
        }
    }


    public function getFormOptions()
    {
        if (!Gate::any(['create', 'update'], Room::class)) {
            abort(403);
        }
        try {

            $formOptions = $this->roomService->getFormOptions();

            return ApiResponse::success(
                'Form options fetched.',
                new RoomFormOptionsResource($formOptions),
                200
            );
        } catch (\Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Failed to fetch Form Options. ' . $e->getMessage(), 500);
        }
    }
    /**
     * Public Action no need to check authorization
     * 
     *
     * @param Room $room
     * @return void
     */
    public function qrView(Room $room)
    {
        $room->load(['location', 'lastCleanedLog']);

        return ApiResponse::success('Room fetched successfully', new RoomResource($room));
    }

    public function upload(UploadRoomRequest $request): JsonResponse
    {
        Gate::authorize('create', Room::class);

        try {
            $createdRooms = $this->roomService->upload($request->validated()['rooms']);

            return ApiResponse::success('Rooms uploaded successfully.', $createdRooms, 201);
        } catch (\Exception $e) {
            Log::error('Failed to upload rooms: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error(
                'Something went wrong while uploading rooms. ' . $e->getMessage(),
                500
            );
        }
    }
}
