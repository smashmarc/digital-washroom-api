<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupLog extends Model
{
    protected $fillable = [
        'backup_schedule_id', 'label', 'type', 'trigger',
        'status', 'filename', 'size_bytes', 'duration_seconds',
        'output', 'error_message', 'started_at', 'finished_at',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(BackupSchedule::class, 'backup_schedule_id');
    }

    /** Human-readable file size */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size_bytes ?? 0;
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return round($bytes / 1048576, 1)  . ' MB';
        return round($bytes / 1024, 0) . ' KB';
    }
}