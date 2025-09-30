<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends SpatieRole
{
    // Add fillable fields if you want to mass-assign
    protected $fillable = [
        'name',
        'guard_name',
        'description',
    ];

    /**
     * Optional: eager load permissions by default
     */
    

    /**
     * Users relationship (if using standard User model with Spatie)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            config('auth.providers.users.model'), 
            'model_has_roles', 
            'role_id', 
            'model_id'
        )->wherePivot('model_type', config('auth.providers.users.model'));
    }
}
