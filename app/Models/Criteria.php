<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Criteria extends Model
{
    protected $table = 'criteria';

    protected $fillable = [
        'criteria_category_id',
        'text',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CriteriaCategory::class, 'criteria_category_id');
    }

    public function evaluationTemplates(): BelongsToMany
    {
        return $this->belongsToMany(EvaluationTemplate::class, 'template_criteria', 'criteria_id', 'evaluation_template_id')
            ->withPivot('order')
            ->withTimestamps();
    }
}
