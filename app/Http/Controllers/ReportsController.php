<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request): JsonResponse
    {
        //Gate::authorize('admin');

        $params = $request->only([
            'type', 'date_from', 'date_to',
            'location_id', 'user_id', 'status_code',
            'per_page', 'page',
        ]);

        $data = match($params['type'] ?? '') {
            'cleaning_activity' => $this->reportService->cleaningActivity($params),
            'room_status'       => $this->reportService->roomStatus($params),
            'user_performance'  => $this->reportService->userPerformance($params),
            'location_summary'  => $this->reportService->locationSummary($params),
            default             => null,
        };

        if (!$data) {
            return ApiResponse::error('Invalid report type.', 422);
        }

        return ApiResponse::success('Report fetched successfully.', $data, 200);
    }

    public function export(Request $request): StreamedResponse
    {
        //Gate::authorize('admin');

        $params = $request->only([
            'type', 'date_from', 'date_to',
            'location_id', 'user_id', 'status_code',
        ]);

        $type = $params['type'] ?? '';

        $rows = match($type) {
            'cleaning_activity' => $this->reportService->cleaningActivityExport($params),
            'room_status'       => $this->reportService->roomStatusExport($params),
            'user_performance'  => $this->reportService->userPerformanceExport($params),
            'location_summary'  => $this->reportService->locationSummaryExport($params),
            default             => null,
        };

        if (!$rows) {
            abort(422, 'Invalid report type.');
        }

        $filename = "{$type}_" . now()->format('Y-m-d') . ".csv";
        $headers  = match($type) {
            'cleaning_activity' => ['Date', 'Location', 'Room', 'User', 'Status', 'Note'],
            'room_status'       => ['Location', 'Room', 'Last Cleaned', 'Last User', 'Status'],
            'user_performance'  => ['User', 'Total Logs', 'Fully Cleaned', 'Partially Cleaned', 'Not Cleaned', 'Last Active'],
            'location_summary'  => ['Location', 'Total Rooms', 'Total Logs', 'Fully Cleaned', 'Partially Cleaned', 'Not Cleaned'],
        };

        $statusMap = [0 => 'Not Cleaned', 1 => 'Partially Cleaned', 2 => 'Fully Cleaned'];

        return response()->streamDownload(function () use ($rows, $headers, $type, $statusMap) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, match($type) {
                    'cleaning_activity' => [
                        $row->logged_at,
                        $row->room?->location?->name,
                        $row->room?->name,
                        $row->user?->name,
                        $statusMap[$row->note_code] ?? 'Unknown',
                        $row->note,
                    ],
                    'room_status' => [
                        $row->location?->name,
                        $row->name,
                        $row->logs->first()?->logged_at,
                        $row->logs->first()?->user?->name,
                        $statusMap[$row->logs->first()?->note_code] ?? '—',
                    ],
                    'user_performance' => [
                        $row->name,
                        $row->total_logs,
                        $row->fully_cleaned,
                        $row->partially_cleaned,
                        $row->not_cleaned,
                        $row->last_active,
                    ],
                    'location_summary' => [
                        $row->name,
                        $row->total_rooms,
                        $row->total_logs,
                        $row->fully_cleaned,
                        $row->partially_cleaned,
                        $row->not_cleaned,
                    ],
                });
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}