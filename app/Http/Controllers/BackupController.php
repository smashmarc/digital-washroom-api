<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\BackupLog;
use App\Models\BackupSchedule;
use App\Services\BackupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    public function __construct(private BackupService $backupService) {}

    // ═══════════════════════════════════════════════
    //  SCHEDULES
    // ═══════════════════════════════════════════════

    /** GET /api/backups/schedules */
    public function schedules(): JsonResponse
    {
        try {
            $schedules = BackupSchedule::with(['logs' => fn($q) => $q->latest()->limit(1)])
                ->latest()
                ->get()
                ->map(fn($s) => $this->formatSchedule($s))
                ->values()
                ->all();

            return ApiResponse::success('Schedules fetched successfully.', $schedules);
        } catch (\Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to fetch schedules. ' . $e->getMessage(), 500);
        }
    }

    /** POST /api/backups/schedules */
    public function storeSchedule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:100',
            'frequency'          => 'required|in:hourly,every_6h,every_12h,daily,weekly,monthly',
            'run_at'             => 'nullable|regex:/^\d{2}:\d{2}$/',
            'run_day'            => 'nullable|integer|min:0|max:31',
            'type'               => 'required|in:full,incremental,schema_only',
            'retention_days'     => 'required|integer|min:1|max:365',
            'is_active'          => 'boolean',
            'utc_offset_minutes' => 'nullable|integer',
        ]);

        if (!empty($validated['run_at'])) {
            $validated['run_at'] = $this->localRunAtToUtc(
                $validated['run_at'],
                (int) ($validated['utc_offset_minutes'] ?? 0)
            );
        }
        unset($validated['utc_offset_minutes']);

        try {
            $schedule = BackupSchedule::create($validated);
            $schedule->update(['next_run_at' => $schedule->computeNextRun()]);

            return ApiResponse::success('Schedule created successfully.', [$this->formatSchedule($schedule)], 201);
        } catch (\Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to create schedule. ' . $e->getMessage(), 500);
        }
    }

    /** PATCH /api/backups/schedules/{id} */
    public function updateSchedule(Request $request, BackupSchedule $schedule): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'sometimes|string|max:100',
            'frequency'          => 'sometimes|in:hourly,every_6h,every_12h,daily,weekly,monthly',
            'run_at'             => 'nullable|regex:/^\d{2}:\d{2}$/',
            'run_day'            => 'nullable|integer',
            'type'               => 'sometimes|in:full,incremental,schema_only',
            'retention_days'     => 'sometimes|integer|min:1|max:365',
            'is_active'          => 'boolean',
            'utc_offset_minutes' => 'nullable|integer',
        ]);

        if (!empty($validated['run_at'])) {
            $validated['run_at'] = $this->localRunAtToUtc(
                $validated['run_at'],
                (int) ($validated['utc_offset_minutes'] ?? 0)
            );
        }
        unset($validated['utc_offset_minutes']);

        try {
            $schedule->update($validated);
            $schedule->update(['next_run_at' => $schedule->computeNextRun()]);

            return ApiResponse::success('Schedule updated successfully.', [$this->formatSchedule($schedule)]);
        } catch (\Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to update schedule. ' . $e->getMessage(), 500);
        }
    }

    /** DELETE /api/backups/schedules/{id} */
    public function destroySchedule(BackupSchedule $schedule): JsonResponse
    {
        try {
            $schedule->delete();
            return ApiResponse::success('Schedule deleted successfully.');
        } catch (\Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to delete schedule. ' . $e->getMessage(), 500);
        }
    }

    /** POST /api/backups/schedules/{id}/run */
    public function runSchedule(BackupSchedule $schedule): JsonResponse
    {
        try {
            $log = $this->backupService->run(
                label: $schedule->name . '_manual-trigger',
                type: $schedule->type,
                trigger: 'manual',
                schedule: $schedule
            );

            return ApiResponse::success('Backup started successfully.', [$this->formatLog($log)], 201);
        } catch (\Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to run backup. ' . $e->getMessage(), 500);
        }
    }

    // ═══════════════════════════════════════════════
    //  MANUAL BACKUPS
    // ═══════════════════════════════════════════════

    /** POST /api/backups/run */
    public function runManual(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:100',
            'type'  => 'required|in:full,incremental,schema_only',
        ]);

        try {
            $log = $this->backupService->run(
                label: $validated['label'],
                type: $validated['type'],
                trigger: 'manual'
            );

            return ApiResponse::success('Backup completed.', [$this->formatLog($log)], 201);
        } catch (\Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to run backup. ' . $e->getMessage(), 500);
        }
    }

    // ═══════════════════════════════════════════════
    //  LOGS / HISTORY
    // ═══════════════════════════════════════════════

    /** GET /api/backups/logs */
    public function logs(Request $request): JsonResponse
    {
        try {
            $logs = BackupLog::with('schedule')
                ->when($request->trigger, fn($q, $v) => $q->where('trigger', $v))
                ->when($request->status,  fn($q, $v) => $q->where('status', $v))
                ->latest('started_at')
                ->paginate($request->per_page ?? 20);

            $items = $logs->map(fn($l) => $this->formatLog($l))->values()->all();

            return ApiResponse::success('Logs fetched successfully.', $items);
        } catch (\Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to fetch logs. ' . $e->getMessage(), 500);
        }
    }

    /** DELETE /api/backups/logs/{log} */
    public function destroyLog(BackupLog $log): JsonResponse
    {
        try {
            $log->delete();
            return ApiResponse::success('Log deleted successfully.');
        } catch (\Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to delete log. ' . $e->getMessage(), 500);
        }
    }

    /** DELETE /api/backups/logs */
    public function clearLogs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trigger' => 'nullable|in:manual,scheduled',
        ]);

        try {
            $query = BackupLog::query();
            if (!empty($validated['trigger'])) {
                $query->where('trigger', $validated['trigger']);
            }
            $count = $query->count();
            $query->delete();
            return ApiResponse::success("Cleared {$count} log(s) successfully.");
        } catch (\Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to clear logs. ' . $e->getMessage(), 500);
        }
    }

    // ═══════════════════════════════════════════════
    //  FILES
    // ═══════════════════════════════════════════════

    /** GET /api/backups/files */
    public function files(): JsonResponse
    {
        try {
            return ApiResponse::success('Files fetched successfully.', $this->backupService->listFiles());
        } catch (\Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to fetch backup files. ' . $e->getMessage(), 500);
        }
    }

    /** DELETE /api/backups/files/{filename} */
    public function deleteFile(string $filename): JsonResponse
    {
        try {
            $deleted = $this->backupService->deleteFile($filename);

            if (!$deleted) {
                return ApiResponse::error('File not found.', 404);
            }

            BackupLog::where('filename', $filename)->update(['filename' => null]);

            return ApiResponse::success('File deleted successfully.');
        } catch (\Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to delete file. ' . $e->getMessage(), 500);
        }
    }

    /** GET /api/backups/files/{filename}/download */
    public function downloadFile(string $filename): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $path = $this->backupService->filePath($filename);
        abort_unless(file_exists($path), 404, 'Backup file not found');
        return response()->download($path, $filename);
    }

    // ═══════════════════════════════════════════════
    //  STATS
    // ═══════════════════════════════════════════════

    /** GET /api/backups/stats */
    public function stats(): JsonResponse
    {
        try {
            $files           = $this->backupService->listFiles();
            $totalSize       = collect($files)->sum('size_bytes');
            $totalBackups    = BackupLog::where('status', 'success')->count();
            $lastBackup      = BackupLog::where('status', 'success')->latest('started_at')->first();
            $activeSchedules = BackupSchedule::where('is_active', true)->count();

            return ApiResponse::success('Stats fetched successfully.', [
                'total_backups'      => $totalBackups,
                'active_schedules'   => $activeSchedules,
                'storage_used_bytes' => $totalSize,
                'storage_used_human' => $this->humanSize($totalSize),
                'last_backup_at'     => $lastBackup?->started_at?->toISOString(),
                'last_backup_ago'    => $lastBackup?->started_at?->diffForHumans(),
            ]);
        } catch (\Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to fetch stats. ' . $e->getMessage(), 500);
        }
    }

    // ─── Private formatters ─────────────────────────────────

    private function formatSchedule(BackupSchedule $s): array
    {
        return [
            'id'             => $s->id,
            'name'           => $s->name,
            'frequency'      => $s->frequency,
            'run_at'         => $s->run_at,
            'run_day'        => $s->run_day,
            'type'           => $s->type,
            'retention_days' => $s->retention_days,
            'is_active'      => $s->is_active,
            'last_run_at'    => $s->last_run_at?->toISOString(),
            'last_run_ago'   => $s->last_run_at?->diffForHumans(),
            'next_run_at'    => $s->next_run_at?->toISOString(),
            'next_run_in'    => $s->next_run_at?->diffForHumans(),
            'last_status'    => $s->last_status,
        ];
    }

    private function formatLog(BackupLog $l): array
    {
        return [
            'id'               => $l->id,
            'schedule_id'      => $l->backup_schedule_id,
            'schedule_name'    => $l->schedule?->name,
            'label'            => $l->label,
            'type'             => $l->type,
            'trigger'          => $l->trigger,
            'status'           => $l->status,
            'filename'         => $l->filename,
            'size_bytes'       => $l->size_bytes,
            'human_size'       => $l->human_size,
            'duration_seconds' => $l->duration_seconds,
            'error_message'    => $l->error_message,
            'started_at'       => $l->started_at?->toISOString(),
            'started_ago'      => $l->started_at?->diffForHumans(),
            'finished_at'      => $l->finished_at?->toISOString(),
        ];
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return round($bytes / 1048576, 1)  . ' MB';
        return round($bytes / 1024, 0) . ' KB';
    }

    private function localRunAtToUtc(string $runAt, int $utcOffsetMinutes): string
    {
        return \Carbon\Carbon::createFromFormat('H:i', $runAt)
            ->subMinutes($utcOffsetMinutes)
            ->format('H:i');
    }
}
