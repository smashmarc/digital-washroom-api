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

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'template_questions')
                    ->withPivot(['order', 'weight_override'])
                    ->orderByPivot('order')
                    ->withTimestamps();
    }

    public function userAssignments(): HasMany
    {
        return $this->hasMany(UserAssignment::class);
    }
}
