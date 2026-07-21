<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'enable_unit_option'];

    protected $casts = [
        'enable_unit_option' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
