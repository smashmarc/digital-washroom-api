<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'pass_score',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'pass_score' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function criteria(): BelongsToMany
    {
        return $this->belongsToMany(Criteria::class, 'template_criteria', 'evaluation_template_id', 'criteria_id')
                    ->withPivot(['order'])
                    ->orderByPivot('order')
                    ->withTimestamps();
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'department_evaluation_template');
    }

    public function userAssignments(): HasMany
    {
        return $this->hasMany(UserAssignment::class);
    }
}
