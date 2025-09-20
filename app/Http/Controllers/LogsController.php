<?php
namespace App\Http\Controllers;
use App\Models\Log;
use App\Helpers\ApiResponse;
use App\Http\Resources\LogResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateLogRequest;
use App\Http\Requests\UpdateLogRequest;
use Illuminate\Http\JsonResponse;


class LogsController extends Controller
{
    public function index(): JsonResponse
    {
        $logs = Log::with(['room', 'user'])->paginate(10);

        return ApiResponse::paginated('Logs fetched successfully', $logs, LogResource::class);
    }

    public function store(CreateLogRequest $request): JsonResponse
    {
        $log = Log::create($request->validated());

        return ApiResponse::success('Log created successfully', new LogResource($log), 201);
    }

    public function show(Log $log): JsonResponse
    {
        return ApiResponse::success('Log fetched successfully', new LogResource($log->load(['room', 'user'])));
    }

    public function update(UpdateLogRequest $request, Log $log): JsonResponse
    {
        $log->update($request->validated());

        return ApiResponse::success('Log updated successfully', new LogResource($log));
    }

    public function destroy(Log $log): JsonResponse
    {
        $log->delete();

        return ApiResponse::success('Log deleted successfully', null, 200);
    }
}
