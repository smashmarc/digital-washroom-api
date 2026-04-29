<?php

namespace App\Console\Commands;

use App\Models\BackupSchedule;
use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunScheduledBackups extends Command
{
    protected $signature   = 'backup:run-scheduled';
    protected $description = 'Execute all due automated backup schedules';

    public function __construct(private BackupService $backupService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        //uncomment if needed 
       // Log::info('backup:run-scheduled fired', ['time' => now()->toDateTimeString()]);

        $due = BackupSchedule::where('is_active', true)
            ->where(fn($q) =>
                $q->whereNull('next_run_at')
                  ->orWhere('next_run_at', '<=', now())
            )
            ->get();

        if ($due->isEmpty()) {
            //Log::info('backup:run-scheduled — no schedules due.');
           // $this->info('No scheduled backups are due.');
            return self::SUCCESS;
        }

        foreach ($due as $schedule) {
            Log::info("backup:run-scheduled — starting: {$schedule->name}");
            $this->info("Running schedule: {$schedule->name}");

            $log = $this->backupService->run(
                label: $schedule->name,
                type: $schedule->type,
                trigger: 'scheduled',
                schedule: $schedule
            );

            if ($log->status === 'success') {
                Log::info("backup:run-scheduled — success: {$schedule->name}", [
                    'size'     => $log->human_size,
                    'duration' => $log->duration_seconds . 's',
                ]);
                $this->info("  ✓ Done — {$log->human_size} in {$log->duration_seconds}s");
            } else {
                Log::error("backup:run-scheduled — failed: {$schedule->name}", [
                    'error' => $log->error_message,
                ]);
                $this->error("  ✗ Failed: {$log->error_message}");
            }
        }

        return self::SUCCESS;
    }
}