<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BackupSchedule extends Model
{
    protected $fillable = [
        'name', 'frequency', 'run_at', 'run_day',
        'type', 'retention_days', 'is_active',
        'last_run_at', 'next_run_at', 'last_status',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(BackupLog::class);
    }

    /** Compute next_run_at based on frequency and run_at */
    public function computeNextRun(): \Carbon\Carbon
    {
        $now   = now();
        $runAt = $this->run_at ?? '02:00:00';

        switch ($this->frequency) {
            case 'hourly':
                return $now->copy()->addHour()->startOfHour();

            case 'every_6h':
                return $now->copy()->addHours(6);

            case 'every_12h':
                return $now->copy()->addHours(12);

            case 'daily': {
                $candidate = $now->copy()->setTimeFromTimeString($runAt);
                return $candidate->isFuture() ? $candidate : $candidate->addDay();
            }

            case 'weekly': {
                $candidate = $now->copy()->setTimeFromTimeString($runAt);
                if ($candidate->isFuture() && $candidate->dayOfWeek === ($this->run_day ?? 0)) {
                    return $candidate;
                }
                return $now->copy()->next($this->run_day ?? 0)->setTimeFromTimeString($runAt);
            }

            case 'monthly': {
                $candidate = $now->copy()->setDay($this->run_day ?? 1)->setTimeFromTimeString($runAt);
                return $candidate->isFuture() ? $candidate : $candidate->addMonth();
            }

            default:
                return $now->copy()->addDay();
        }
    }
}