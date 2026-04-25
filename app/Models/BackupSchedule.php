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
        $now = now();

        return match ($this->frequency) {
            'hourly'     => $now->copy()->addHour()->startOfHour(),
            'every_6h'   => $now->copy()->addHours(6),
            'every_12h'  => $now->copy()->addHours(12),
            'daily'      => $now->copy()->addDay()->setTimeFromTimeString($this->run_at ?? '02:00:00'),
            'weekly'     => $now->copy()->next($this->run_day ?? 0)->setTimeFromTimeString($this->run_at ?? '02:00:00'),
            'monthly'    => $now->copy()->addMonth()->setDay($this->run_day ?? 1)->setTimeFromTimeString($this->run_at ?? '02:00:00'),
            default      => $now->copy()->addDay(),
        };
    }
}