<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Report;
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
        Gate::authorize('view', Report::class);

        $params = $request->only([
            'type', 'date_from', 'date_to',
            'location_id', 'user_id', 'status_code', 'department_id', 'unit_id',
            'per_page', 'page',
        ]);

        $data = match($params['type'] ?? '') {
            'cleaning_activity'        => $this->reportService->cleaningActivity($params),
            'room_status'              => $this->reportService->roomStatus($params),
            'user_performance'         => $this->reportService->userPerformance($params),
            'location_summary'         => $this->reportService->locationSummary($params),
            'location_detail'          => $this->reportService->locationDetail($params),
            'qa_location_summary'      => $this->reportService->qaLocationSummary($params),
            'qa_employee_performance'  => $this->reportService->qaEmployeePerformance($params),
            'qa_template_analysis'     => $this->reportService->qaTemplateAnalysis($params),
            'qa_evaluator_activity'    => $this->reportService->qaEvaluatorActivity($params),
            'qa_department_summary'    => $this->reportService->qaDepartmentSummary($params),
            default                    => null,
        };

        if (!$data) {
            return ApiResponse::error('Invalid report type.', 422);
        }

        return ApiResponse::success('Report fetched successfully.', $data, 200);
    }

    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('export', Report::class);

        $params = $request->only([
            'type', 'date_from', 'date_to',
            'location_id', 'user_id', 'status_code', 'department_id', 'unit_id',
        ]);

        $type = $params['type'] ?? '';

        $rows = match($type) {
            'cleaning_activity'       => $this->reportService->cleaningActivityExport($params),
            'room_status'             => $this->reportService->roomStatusExport($params),
            'user_performance'        => $this->reportService->userPerformanceExport($params),
            'location_summary'        => $this->reportService->locationSummaryExport($params),
            'location_detail'         => $this->reportService->locationDetailExport($params),
            'qa_location_summary'     => $this->reportService->qaLocationSummary(array_merge($params, ['per_page' => PHP_INT_MAX]))->getCollection(),
            'qa_employee_performance' => $this->reportService->qaEmployeePerformance(array_merge($params, ['per_page' => PHP_INT_MAX]))->getCollection(),
            'qa_template_analysis'    => $this->reportService->qaTemplateAnalysis(array_merge($params, ['per_page' => PHP_INT_MAX]))->getCollection(),
            'qa_evaluator_activity'   => $this->reportService->qaEvaluatorActivity(array_merge($params, ['per_page' => PHP_INT_MAX]))->getCollection(),
            'qa_department_summary'   => $this->reportService->qaDepartmentSummary(array_merge($params, ['per_page' => PHP_INT_MAX]))->getCollection(),
            default                   => null,
        };

        if (!$rows) {
            abort(422, 'Invalid report type.');
        }

        $filename = "{$type}_" . now()->format('Y-m-d') . ".csv";
        $headers  = match($type) {
            'cleaning_activity'       => ['Date', 'Location', 'Room', 'User', 'Status', 'Note'],
            'room_status'             => ['Location', 'Room', 'Last Cleaned', 'Last User', 'Status'],
            'user_performance'        => ['User', 'Total Logs', 'Fully Cleaned', 'Partially Cleaned', 'Not Cleaned', 'Last Active'],
            'location_summary'        => ['Location', 'Total Rooms', 'Total Logs', 'Fully Cleaned', 'Partially Cleaned', 'Not Cleaned'],
            'location_detail'         => ['Location', 'Room', 'Total Logs', 'Fully Cleaned', 'Partially Cleaned', 'Not Cleaned', 'Last Cleaned'],
            'qa_location_summary'     => ['Location', 'Evaluations', 'Avg Score', 'Passed', 'Failed', 'Inconclusive', 'Pass Rate (%)'],
            'qa_employee_performance' => ['Employee', 'Total Evaluations', 'Avg Score', 'Passed', 'Failed', 'Inconclusive', 'Last Evaluated'],
            'qa_template_analysis'    => ['Template', 'Total Evaluations', 'Avg Score', 'Passed', 'Failed', 'Inconclusive'],
            'qa_evaluator_activity'   => ['Evaluator', 'Total Conducted', 'Avg Score', 'Passed', 'Failed', 'Inconclusive', 'Last Conducted'],
            'qa_department_summary'   => ['Department', 'Total Evaluations', 'Avg Score', 'Passed', 'Failed', 'Inconclusive', 'Pass Rate (%)'],
        };

        $statusMap = [0 => 'Not Cleaned', 1 => 'Partially Cleaned', 2 => 'Fully Cleaned'];

        return response()->streamDownload(function () use ($rows, $headers, $type, $statusMap) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                $r = is_array($row) ? (object) $row : $row;
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
                        $row->name, $row->total_logs, $row->fully_cleaned,
                        $row->partially_cleaned, $row->not_cleaned, $row->last_active,
                    ],
                    'location_summary' => [
                        $row->name, $row->total_rooms, $row->total_logs,
                        $row->fully_cleaned, $row->partially_cleaned, $row->not_cleaned,
                    ],
                    'location_detail' => [
                        $row->location?->name, $row->name, $row->total_logs,
                        $row->fully_cleaned, $row->partially_cleaned, $row->not_cleaned,
                        $row->last_cleaned,
                    ],
                    'qa_employee_performance' => [
                        $r->name, $r->total_evaluations, $r->avg_score ?? '—',
                        $r->passed, $r->failed, $r->inconclusive, $r->last_evaluated ?? '—',
                    ],
                    'qa_template_analysis' => [
                        $r->name, $r->total_evaluations, $r->avg_score ?? '—',
                        $r->passed, $r->failed, $r->inconclusive,
                    ],
                    'qa_evaluator_activity' => [
                        $r->name, $r->total_conducted, $r->avg_score ?? '—',
                        $r->passed, $r->failed, $r->inconclusive, $r->last_conducted ?? '—',
                    ],
                    'qa_location_summary' => [
                        $r->name, $r->total, $r->avg_score ?? '—',
                        $r->passed, $r->failed, $r->inconclusive,
                        $r->total > 0 ? round(($r->passed / $r->total) * 100, 1) : '—',
                    ],
                    'qa_department_summary' => [
                        $r->name, $r->total, $r->avg_score ?? '—',
                        $r->passed, $r->failed, $r->inconclusive,
                        $r->total > 0 ? round(($r->passed / $r->total) * 100, 1) : '—',
                    ],
                });
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}