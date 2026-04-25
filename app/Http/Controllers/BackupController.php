<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BackupLog;
use App\Models\BackupSchedule;
use App\Services\BackupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function __construct(private BackupService $backupService) {}

    // ═══════════════════════════════════════════════
    //  SCHEDULES
    // ═══════════════════════════════════════════════

    /** GET /api/backups/schedules */
    public function schedules(): JsonResponse
    {
        $schedules = BackupSchedule::with(['logs' => fn($q) => $q->latest()->limit(1)])
            ->latest()
            ->get()
            ->map(fn($s) => $this->formatSchedule($s));

        return response()->json(['data' => $schedules]);
    }

    /** POST /api/backups/schedules */
    public function storeSchedule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'frequency'      => 'required|in:hourly,every_6h,every_12h,daily,weekly,monthly',
            'run_at'         => 'nullable|date_format:H:i',
            'run_day'        => 'nullable|integer|min:0|max:31',
            'type'           => 'required|in:full,incremental,schema_only',
            'retention_days' => 'required|integer|min:1|max:365',
            'is_active'      => 'boolean',
        ]);

        $schedule = BackupSchedule::create($validated);
        $schedule->update(['next_run_at' => $schedule->computeNextRun()]);

        return response()->json(['data' => $this->formatSchedule($schedule)], 201);
    }

    /** PATCH /api/backups/schedules/{id} */
    public function updateSchedule(Request $request, BackupSchedule $schedule): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'sometimes|string|max:100',
            'frequency'      => 'sometimes|in:hourly,every_6h,every_12h,daily,weekly,monthly',
            'run_at'         => 'nullable|date_format:H:i',
            'run_day'        => 'nullable|integer',
            'type'           => 'sometimes|in:full,incremental,schema_only',
            'retention_days' => 'sometimes|integer|min:1|max:365',
            'is_active'      => 'boolean',
        ]);

        $schedule->update($validated);
        $schedule->update(['next_run_at' => $schedule->computeNextRun()]);

        return response()->json(['data' => $this->formatSchedule($schedule)]);
    }

    /** DELETE /api/backups/schedules/{id} */
    public function destroySchedule(BackupSchedule $schedule): JsonResponse
    {
        $schedule->delete();
        return response()->json(['message' => 'Schedule deleted']);
    }

    /** POST /api/backups/schedules/{id}/run  — trigger a scheduled backup immediately */
    public function runSchedule(BackupSchedule $schedule): JsonResponse
    {
        $log = $this->backupService->run(
            label: $schedule->name . '_manual-trigger',
            type: $schedule->type,
            trigger: 'manual',
            schedule: $schedule
        );

        return response()->json(['data' => $this->formatLog($log)]);
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

        $log = $this->backupService->run(
            label: $validated['label'],
            type: $validated['type'],
            trigger: 'manual'
        );

        return response()->json(['data' => $this->formatLog($log)], 201);
    }

    // ═══════════════════════════════════════════════
    //  LOGS / HISTORY
    // ═══════════════════════════════════════════════

    /** GET /api/backups/logs */
    public function logs(Request $request): JsonResponse
    {
        $logs = BackupLog::with('schedule')
            ->when($request->trigger, fn($q, $v) => $q->where('trigger', $v))
            ->when($request->status,  fn($q, $v) => $q->where('status', $v))
            ->latest('started_at')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $logs->map(fn($l) => $this->formatLog($l)),
            'meta' => [
                'total'        => $logs->total(),
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
            ],
        ]);
    }

    // ═══════════════════════════════════════════════
    //  FILES
    // ═══════════════════════════════════════════════

    /** GET /api/backups/files */
    public function files(): JsonResponse
    {
        return response()->json(['data' => $this->backupService->listFiles()]);
    }

    /** DELETE /api/backups/files/{filename} */
    public function deleteFile(string $filename): JsonResponse
    {
        $deleted = $this->backupService->deleteFile($filename);

        if (!$deleted) {
            return response()->json(['message' => 'File not found'], 404);
        }

        // Also mark the log entry
        BackupLog::where('filename', $filename)->update(['filename' => null]);

        return response()->json(['message' => 'File deleted']);
    }

    /** GET /api/backups/files/{filename}/download */
    public function downloadFile(string $filename): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $path = $this->backupService->filePath($filename);

        abort_unless(file_exists($path), 404, 'Backup file not found');

        return response()->download($path, $filename);
    }

    // ═══════════════════════════════════════════════
    //  Stats
    // ═══════════════════════════════════════════════

    /** GET /api/backups/stats */
    public function stats(): JsonResponse
    {
        $files       = $this->backupService->listFiles();
        $totalSize   = collect($files)->sum('size_bytes');
        $totalBackups = BackupLog::where('status', 'success')->count();
        $lastBackup  = BackupLog::where('status', 'success')->latest('started_at')->first();
        $activeSchedules = BackupSchedule::where('is_active', true)->count();

        return response()->json([
            'data' => [
                'total_backups'      => $totalBackups,
                'active_schedules'   => $activeSchedules,
                'storage_used_bytes' => $totalSize,
                'storage_used_human' => $this->humanSize($totalSize),
                'last_backup_at'     => $lastBackup?->started_at?->toISOString(),
                'last_backup_ago'    => $lastBackup?->started_at?->diffForHumans(),
            ],
        ]);
    }

    // ─── Formatters ─────────────────────────────────

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
}