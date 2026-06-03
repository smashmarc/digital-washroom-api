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
        'type',
        'weight',
        'is_fatal',
        'is_active',
    ];

    protected $casts = [
        'is_fatal'  => 'boolean',
        'is_active' => 'boolean',
        'weight'    => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(QuestionCategory::class, 'question_category_id');
    }

    public function evaluationTemplates(): BelongsToMany
    {
        return $this->belongsToMany(EvaluationTemplate::class, 'template_questions')
                    ->withPivot(['order', 'weight_override'])
                    ->withTimestamps();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(EvaluationAnswer::class);
    }
}
