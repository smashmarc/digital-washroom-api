<?php

namespace App\Services;

use App\Models\BackupLog;
use App\Models\BackupSchedule;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackupService
{
    protected string $disk = 'local';
    protected string $folder = 'backups';

    /**
     * Run a backup (manual or scheduled).
     * Returns the persisted BackupLog.
     */
    public function run(
        string $label,
        string $type = 'full',
        string $trigger = 'manual',
        ?BackupSchedule $schedule = null
    ): BackupLog {
        $log = BackupLog::create([
            'backup_schedule_id' => $schedule?->id,
            'label'              => $label,
            'type'               => $type,
            'trigger'            => $trigger,
            'status'             => 'running',
            'started_at'         => now(),
        ]);

        if ($schedule) {
            $schedule->update(['last_status' => 'running']);
        }

        try {
            $filename = $this->buildFilename($label, $type);
            $start    = microtime(true);
            $output   = $this->dumpDatabase($type, $filename);
            $duration = (int) (microtime(true) - $start);
            $sizePath = Storage::disk($this->disk)->path("{$this->folder}/{$filename}");
            $sizeBytes = file_exists($sizePath) ? filesize($sizePath) : 0;

            $log->update([
                'status'           => 'success',
                'filename'         => $filename,
                'size_bytes'       => $sizeBytes,
                'duration_seconds' => $duration,
                'output'           => $output,
                'finished_at'      => now(),
            ]);

            if ($schedule) {
                $schedule->update([
                    'last_run_at'  => now(),
                    'last_status'  => 'success',
                    'next_run_at'  => $schedule->computeNextRun(),
                ]);
            }

            // Prune old backups for this schedule
            if ($schedule) {
                $this->pruneOldBackups($schedule);
            }

            // TODO: re-enable when transfer destinations are configured
            // app(BackupTransferService::class)->transferToAll($filename);
        } catch (\Throwable $e) {
            $log->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at'   => now(),
            ]);

            if ($schedule) {
                $schedule->update(['last_status' => 'failed']);
            }
        }

        return $log->fresh();
    }

    /**
     * List all backup files on disk with metadata.
     */
    public function listFiles(): array
    {
        $files = Storage::disk($this->disk)->files($this->folder);
        $result = [];

        foreach ($files as $file) {
            $path = Storage::disk($this->disk)->path($file);
            $result[] = [
                'filename'   => basename($file),
                'path'       => $file,
                'size_bytes' => filesize($path),
                'human_size' => $this->humanSize(filesize($path)),
                'created_at' => date('Y-m-d H:i:s', filemtime($path)),
            ];
        }

        usort($result, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
        return $result;
    }

    /**
     * Delete a backup file from disk.
     */
    public function deleteFile(string $filename): bool
    {
        $path = "{$this->folder}/{$filename}";
        if (Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
            return true;
        }
        return false;
    }

    /**
     * Get the full path for download.
     */
    public function filePath(string $filename): string
    {
        return Storage::disk($this->disk)->path("{$this->folder}/{$filename}");
    }

    // ─────────────── Private helpers ────────────────

    private function buildFilename(string $label, string $type): string
    {
        $slug = Str::slug($label);
        $date = now()->format('Y-m-d_H-i');
        return "backup_{$slug}_{$type}_{$date}.sql.gz";
    }

    private function dumpDatabase(string $type, string $filename): string
    {
        $config  = config('database.connections.' . config('database.default'));
        $host    = $config['host']     ?? '127.0.0.1';
        $port    = $config['port']     ?? 3306;
        $dbname  = $config['database'] ?? '';
        $user    = $config['username'] ?? '';
        $pass    = $config['password'] ?? '';
        $driver  = $config['driver']   ?? 'mysql';

        $outPath = Storage::disk($this->disk)->path("{$this->folder}/{$filename}");

        // Ensure folder exists
        Storage::disk($this->disk)->makeDirectory($this->folder);

        $typeFlag = match ($type) {
            'schema_only'  => '--no-data',
            'incremental'  => '--no-create-info',  // data-only as incremental proxy
            default        => '',
        };

        $escapedPass = escapeshellarg($pass);

        if ($driver === 'pgsql') {
            $inner = "PGPASSWORD={$escapedPass} pg_dump -h {$host} -p {$port} -U {$user} {$typeFlag} {$dbname} | gzip > {$outPath}";
        } else {
            $inner = "mysqldump -h {$host} -P {$port} -u {$user} -p{$escapedPass} --ssl=0 {$typeFlag} {$dbname} | gzip > {$outPath}";
        }

        // pipefail ensures we get mysqldump's exit code, not gzip's
        $cmd = "bash -c 'set -o pipefail; {$inner}' 2>&1";

        $output = [];
        $code   = 0;
        exec($cmd, $output, $code);

        if ($code !== 0) {
            // Clean up empty file if dump failed
            if (file_exists($outPath)) {
                unlink($outPath);
            }
            throw new \RuntimeException('Dump failed (exit ' . $code . '): ' . implode("\n", $output));
        }

        return implode("\n", $output);
    }

    private function pruneOldBackups(BackupSchedule $schedule): void
    {
        $logs = BackupLog::where('backup_schedule_id', $schedule->id)
            ->where('status', 'success')
            ->whereNotNull('filename')
            ->orderByDesc('started_at')
            ->get();

        $cutoff = now()->subDays($schedule->retention_days);

        foreach ($logs as $log) {
            if ($log->started_at->lt($cutoff)) {
                $this->deleteFile($log->filename);
                $log->update(['filename' => null]);
            }
        }
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return round($bytes / 1048576, 1)  . ' MB';
        return round($bytes / 1024, 0) . ' KB';
    }
}