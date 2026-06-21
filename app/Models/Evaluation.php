<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Department;

class Evaluation extends Model
{
    protected $fillable = [
        'user_id',
        'department_id',
        'location_id',
        'unit_id',
        'room_name',
        'evaluation_template_id',
        'evaluator_id',
        'updated_by',
        'score',
        'result',
        'status',
        'pass_score',
        'overall_notes',
        'submitted_at',
    ];

    protected $casts = [
        'score'        => 'float',
        'pass_score'   => 'integer',
        'submitted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Location::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Unit::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EvaluationTemplate::class, 'evaluation_template_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(EvaluationAnswer::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
