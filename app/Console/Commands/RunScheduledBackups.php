<?php

namespace App\Console\Commands;

use App\Models\BackupSchedule;
use App\Services\BackupService;
use Illuminate\Console\Command;

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
        $due = BackupSchedule::where('is_active', true)
            ->where(fn($q) =>
                $q->whereNull('next_run_at')
                  ->orWhere('next_run_at', '<=', now())
            )
            ->get();

        if ($due->isEmpty()) {
            $this->info('No scheduled backups are due.');
            return self::SUCCESS;
        }

        foreach ($due as $schedule) {
            $this->info("Running schedule: {$schedule->name}");

            $log = $this->backupService->run(
                label: $schedule->name,
                type: $schedule->type,
                trigger: 'scheduled',
                schedule: $schedule
            );

            if ($log->status === 'success') {
                $this->info("  ✓ Done — {$log->human_size} in {$log->duration_seconds}s");
            } else {
                $this->error("  ✗ Failed: {$log->error_message}");
            }
        }

        return self::SUCCESS;
    }
}