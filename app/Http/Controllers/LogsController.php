<?php
namespace App\Http\Controllers;
use Exception;
use App\Models\Log;
use App\Helpers\ApiResponse;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        Gate::authorize('view', new Log());
         $params = $request->only([
            'search',
            'sort_by',
            'sort_dir',
            'per_page',
            'with',
            'columns',
            'exact',
            'date_from',
            'date_to',
        ]);
        
        try {
            $items = $this->logService->searchPaginatedList($params);
            return ApiResponse::success(
                'Logs fetched successfully.',
                $items,
                200,
                LogResource::class
            );
           
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch logs.'.$e->getMessage(), 500);
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
        try {      
            $updated = $this->logService->update($request->validated(), $log);
            return ApiResponse::success('Log updated successfully', new LogResource($updated));
        } catch (Exception $e) {
            return ApiResponse::error('Failed to update log.'.$e->getMessage(), 500);
        }
    }

    public function destroy(Log $log): JsonResponse
    {
        Gate::authorize('delete', $log);
        try {
            $this->logService->delete($log);
            return ApiResponse::success('Log deleted successfully', null, 200);
        } catch (Exception $e) {
            return ApiResponse::error('Failed to delete log.'.$e->getMessage(), 500);
        }
    }

    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('export', Log::class);

        $params = $request->only(['search', 'date_from', 'date_to', 'sort_dir']);
        $logs   = $this->logService->exportAll($params);

        $statusMap = [0 => 'Not Cleaned', 1 => 'Partially Cleaned', 2 => 'Fully Cleaned'];
        $filename  = 'logs_' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($logs, $statusMap) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Location', 'Room', 'User', 'Status', 'Note', 'Logged At']);
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->room?->location?->name ?? '—',
                    $log->room?->name            ?? '—',
                    $log->user?->name            ?? '—',
                    $statusMap[$log->note_code]  ?? 'Unknown',
                    $log->note                   ?? '',
                    $log->logged_at              ?? '',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
