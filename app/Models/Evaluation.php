<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluation extends Model
{
    protected $fillable = [
        'user_assignment_id',
        'evaluator_id',
        'score',
        'result',
        'status',
        'fatal_failed',
        'overall_notes',
        'submitted_at',
    ];

    protected $casts = [
        'score'        => 'float',
        'fatal_failed' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(UserAssignment::class, 'user_assignment_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(EvaluationAnswer::class);
    }
}
