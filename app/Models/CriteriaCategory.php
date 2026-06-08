<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CriteriaCategory extends Model
{
    protected $table = 'criteria_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function criteria(): HasMany
    {
        return $this->hasMany(Criteria::class, 'criteria_category_id');
    }
}
