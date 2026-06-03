<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'question_category_id',
        'text',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(QuestionCategory::class, 'question_category_id');
    }

    public function evaluationTemplates(): BelongsToMany
    {
        return $this->belongsToMany(EvaluationTemplate::class, 'template_questions')
                    ->withPivot(['order'])
                    ->withTimestamps();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(EvaluationAnswer::class);
    }
}
