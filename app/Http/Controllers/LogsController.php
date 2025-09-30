<?php
namespace App\Http\Controllers;
use Exception;
use App\Models\Log;
use App\Helpers\ApiResponse;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Http\Resources\LogResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\CreateLogRequest;
use App\Http\Requests\UpdateLogRequest;


class LogsController extends Controller
{
    protected $logService;
    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('view', Log::class);
         $params = $request->only([
            'search',
            'sort_by',
            'sort_dir',
            'per_page',
            'with',
            'columns',
            'exact'
        ]);
        $columns = ['name'];
        try {
            $items = $this->logService->searchPaginatedList($params, $columns);
            return ApiResponse::success(
                'Logs fetched successfully.',
                $items,
                200,
                LogResource::class
            );
           
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch logs.', 500);
        }
        
    }

    public function store(CreateLogRequest $request): JsonResponse
    {
        $log = Log::create($request->validated());

        return ApiResponse::success('Log created successfully', new LogResource($log), 201);
    }

    public function show(Log $log): JsonResponse
    {
        Gate::authorize('view', Log::class);
        return ApiResponse::success('Log fetched successfully', new LogResource($log->load(['room', 'user'])));
    }

    public function update(UpdateLogRequest $request, Log $log): JsonResponse
    {
        $log->update($request->validated());

        return ApiResponse::success('Log updated successfully', new LogResource($log));
    }

    public function destroy(Log $log): JsonResponse
    {
        Gate::authorize('delete', Log::class);
        $log->delete();

        return ApiResponse::success('Log deleted successfully', null, 200);
    }
}
